<?php
// services/bookingService.php

require_once __DIR__ . '/../utils/booking_helper.php';
require_once __DIR__ . '/roomAllocationService.php';
require_once __DIR__ . '/../db.php';
// ถ้าจะใช้ส่งเมล ให้ require PHPMailer
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

/**
 * ดึง booking ตาม id
 */
function getBookingById(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

function getAdminBookingList(mysqli $conn): mysqli_result
{
    $sql = "
        SELECT b.*,
            (
                SELECT d.file_path
                FROM booking_documents d
                WHERE d.booking_id = b.id
                  AND d.uploaded_by = 'admin'
                  AND d.is_visible_to_user = 1
                ORDER BY d.uploaded_at DESC
                LIMIT 1
            ) AS admin_doc_path
        FROM bookings b
        ORDER BY b.id DESC
    ";

    $result = $conn->query($sql);
    if (!$result) {
        // เผื่อ debug
        throw new RuntimeException('Query booking list failed: ' . $conn->error);
    }

    return $result;
}

/**
 * อัปเดตข้อมูลการจองจากฟอร์ม admin
 * return [bool $ok, array $errors, array $updatedFields]
 */
function updateBooking(mysqli $conn, int $bookingId, array $post): array
{
    $errors = [];

    // map input
    $fullName = trim($post['full_name'] ?? '');
    $phone    = trim($post['phone'] ?? '');
    $lineId   = trim($post['line_id'] ?? '');
    $email    = trim($post['email'] ?? '');

    $position       = $post['position'] ?? null;
    $studentYearRaw = $post['student_year'] ?? null;
    $positionOther  = trim($post['position_other'] ?? '');

    $department   = trim($post['department'] ?? '');
    $purpose      = $post['purpose'] ?? null;
    $studyCourse  = trim($post['study_course'] ?? '');
    $studyDept    = trim($post['study_dept'] ?? '');
    $electiveDept = trim($post['elective_dept'] ?? '');

    $checkInRaw  = $post['check_in_date'] ?? null;
    $checkOutRaw = $post['check_out_date'] ?? null;
    $checkIn     = toSqlDate($checkInRaw);
    $checkOut    = toSqlDate($checkOutRaw);

    $womanCount = isset($post['woman_count']) ? (int)$post['woman_count'] : 0;
    $manCount   = isset($post['man_count'])   ? (int)$post['man_count']   : 0;

    // validate
    if ($fullName === '') {
        $errors[] = 'กรุณากรอกชื่อผู้จอง';
    }
    if ($email === '') {
        $errors[] = 'กรุณากรอกอีเมล';
    }
    if (!$checkIn || !$checkOut) {
        $errors[] = 'กรุณาระบุวันที่เข้าพักและวันที่ย้ายออกให้ถูกต้อง';
    } elseif ($checkOut < $checkIn) {
        $errors[] = 'วันที่ย้ายออกต้องไม่น้อยกว่าวันที่เข้าพัก';
    }
    if ($womanCount < 0 || $manCount < 0) {
        $errors[] = 'จำนวนผู้เข้าพักต้องไม่ติดลบ';
    }

    if ($position !== 'student') {
        $studentYear = null;
    } else {
        $studentYear = ($studentYearRaw === '' || $studentYearRaw === null)
            ? null
            : (int)$studentYearRaw;
    }

    if ($position !== 'other') {
        $positionOther = null;
    }

    if (!empty($errors)) {
        return [false, $errors, []];
    }

    $sqlUpdate = "
        UPDATE bookings
        SET
            full_name      = ?,
            phone          = ?,
            line_id        = ?,
            email          = ?,
            position       = ?,
            student_year   = ?,
            position_other = ?,
            department     = ?,
            purpose        = ?,
            study_course   = ?,
            study_dept     = ?,
            elective_dept  = ?,
            check_in_date  = ?,
            check_out_date = ?,
            woman_count    = ?,
            man_count      = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sqlUpdate);
    if (!$stmt) {
        return [false, ['ไม่สามารถเตรียมคำสั่งฐานข้อมูลได้: ' . $conn->error], []];
    }

    $stmt->bind_param(
        'sssssissssssssiii',
        $fullName,
        $phone,
        $lineId,
        $email,
        $position,
        $studentYear,
        $positionOther,
        $department,
        $purpose,
        $studyCourse,
        $studyDept,
        $electiveDept,
        $checkIn,
        $checkOut,
        $womanCount,
        $manCount,
        $bookingId
    );

    if (!$stmt->execute()) {
        $stmt->close();
        return [false, ['บันทึกไม่สำเร็จ: ' . $stmt->error], []];
    }
    $stmt->close();

    $updated = [
        'full_name'      => $fullName,
        'phone'          => $phone,
        'line_id'        => $lineId,
        'email'          => $email,
        'position'       => $position,
        'student_year'   => $studentYear,
        'position_other' => $positionOther,
        'department'     => $department,
        'purpose'        => $purpose,
        'study_course'   => $studyCourse,
        'study_dept'     => $studyDept,
        'elective_dept'  => $electiveDept,
        'check_in_date'  => $checkIn,
        'check_out_date' => $checkOut,
        'woman_count'    => $womanCount,
        'man_count'      => $manCount,
    ];

    return [true, [], $updated];
}




/**
 * ลบ booking + room_allocations + booking_documents (และไฟล์)
 */
function deleteBooking(mysqli $conn, int $bookingId): bool
{
    try {
        $conn->begin_transaction();

        // ดึงรายการไฟล์เอกสารเพื่อเอาไว้ unlink
        $stmt = $conn->prepare("
            SELECT file_path
            FROM booking_documents
            WHERE booking_id = ?
        ");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $res = $stmt->get_result();

        $paths = [];
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['file_path'])) {
                $paths[] = $row['file_path'];
            }
        }
        $stmt->close();

        // ลบ allocations
        $stmt = $conn->prepare("DELETE FROM room_allocations WHERE booking_id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $stmt->close();

        // ลบเอกสาร
        $stmt = $conn->prepare("DELETE FROM booking_documents WHERE booking_id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $stmt->close();

        // ลบ booking
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected <= 0) {
            $conn->rollback();
            return false;
        }

        $conn->commit();

        // ลบไฟล์ในเครื่อง (หลัง commit)
        foreach ($paths as $p) {
            // file_path เก็บแบบ 'uploads/documents/xxx.pdf'
            $full = __DIR__ . '/../' . ltrim($p, '/');
            if (is_file($full)) {
                @unlink($full);
            }
        }

        return true;
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('deleteBooking error: ' . $e->getMessage());
        return false;
    }
}

/**
 * อนุมัติ booking:
 *  - gen token
 *  - อัปเดต status + token + expired
 *  - ลบ allocations เดิม + จัดห้องใหม่ (strict)
 *  return booking array สำหรับใช้ส่งเมล หรือ null ถ้า fail
 */
function approveBooking(mysqli $conn, int $bookingId): ?array
{
    try {
        $conn->begin_transaction();

        // 1) อัปเดต booking เป็น approved + เก็บ token
        $stmt = $conn->prepare("
            UPDATE bookings
            SET status = 'approved',
                reject_reason = NULL,
            WHERE id = ?
        ");
        $stmt->bind_param('ssi', $token, $expire, $bookingId);
        if (!$stmt->execute()) {
            $stmt->close();
            $conn->rollback();
            return null;
        }
        $stmt->close();

        // 2) ลบ allocations เดิม (กันกรณีมีอยู่แล้ว)
        $stmt = $conn->prepare("DELETE FROM room_allocations WHERE booking_id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $stmt->close();

        // 3) จัดห้องใหม่แบบ strict
        $okAlloc = allocateRoomsStrict($conn, $bookingId);
        if (!$okAlloc) {
            $conn->rollback();
            return ['__error__' => 'no_rooms'];
        }

        // 4) เติมรายชื่อผู้เข้าพักจาก booking_guest_requests ลง room_guests
        if (!autoFillRoomGuestsFromRequests($conn, $bookingId)) {
            $conn->rollback();
            return null;
        }

        // 5) ทุกอย่างผ่าน → commit
        $conn->commit();

        // 6) ดึงข้อมูล booking ที่ต้องใช้ส่งเมลกลับไป
        $booking = getBookingById($conn, $bookingId);
        return $booking ?: null;
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('approveBooking error: ' . $e->getMessage());
        return null;
    }
}


/**
 * ไม่อนุมัติ booking
 * return booking array (สำหรับส่งเมล) หรือ null ถ้า fail
 */
function rejectBooking(mysqli $conn, int $bookingId, string $reason): ?array
{
    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'rejected', reject_reason = ?
        WHERE id = ?
    ");
    $stmt->bind_param('si', $reason, $bookingId);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $stmt->close();

    return getBookingById($conn, $bookingId);
}
/**
 * ดึงรายชื่อผู้เข้าพักที่กรอกในฟอร์มคำขอ
 */
function getGuestRequestsByBookingId(mysqli $conn, int $bookingId): array
{
    $sql = "
        SELECT id, guest_name, gender, guest_phone
        FROM booking_guest_requests
        WHERE booking_id = ?
        ORDER BY id
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    return $rows;
}

/**
 * ตั้งสถานะกลับเป็น pending
 */
function setBookingPending(mysqli $conn, int $bookingId): bool
{
    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'pending', reject_reason = NULL
        WHERE id = ?
    ");
    $stmt->bind_param('i', $bookingId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
