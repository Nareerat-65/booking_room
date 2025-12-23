<?php
// services/bookingService.php

require_once __DIR__ . '/../utils/booking_helper.php';
require_once __DIR__ . '/roomAllocationService.php';
require_once __DIR__ . '/../db.php';

require_once __DIR__ . '/../repositories/bookingRepo.php';
require_once __DIR__ . '/../repositories/guestRequestRepo.php';
require_once __DIR__ . '/../repositories/documentRepo.php';


/**
 * ดึง booking ตาม id
 */
function getBookingById(mysqli $conn, int $id): ?array
{
    return booking_findById($conn, $id);
}

function getAdminBookingList(mysqli $conn): mysqli_result
{
    return booking_listAdmin($conn);
}

function getBookingGuestRequests(mysqli $conn, int $bookingId): array
{
    return guestRequest_listByBookingId($conn, $bookingId);
}

/**
 * แทนที่รายชื่อทั้งหมดของ booking นี้ด้วยชุดใหม่ (ง่ายและชัวร์สุด)
 */
function replaceBookingGuestRequests(mysqli $conn, int $bookingId, array $guests): void
{
    guestRequest_replaceAll($conn, $bookingId, $guests);
}

/**
 * แปลง POST -> guests array
 * รับรูปแบบ input เป็น guest_name[], guest_gender[], guest_phone[]
 */
function parseGuestPost(array $post): array
{
    $names  = $post['guest_name']  ?? [];
    $genders = $post['guest_gender'] ?? [];
    $phones = $post['guest_phone'] ?? [];

    if (!is_array($names) || !is_array($genders) || !is_array($phones)) return [];

    $out = [];
    $n = max(count($names), count($genders), count($phones));

    for ($i = 0; $i < $n; $i++) {
        $out[] = [
            'guest_name'  => $names[$i]   ?? '',
            'gender'      => $genders[$i] ?? null,
            'guest_phone' => $phones[$i]  ?? '',
        ];
    }
    return $out;
}


/**
 * อัปเดตข้อมูลการจองจากฟอร์ม admin
 * return [bool $ok, array $errors, array $updatedFields]
 */
function updateBooking(mysqli $conn, int $bookingId, array $post): array
{
    $errors  = [];
    $updated = []; // ถ้ายังไม่ทำ diff ก็คืน [] ได้ก่อน

    // map input
    $fullName = trim((string)($post['full_name'] ?? ''));
    $phone    = trim((string)($post['phone'] ?? ''));
    $lineId   = trim((string)($post['line_id'] ?? ''));
    $email    = trim((string)($post['email'] ?? ''));

    $position       = $post['position'] ?? null;
    $studentYearRaw = $post['student_year'] ?? null;
    $positionOther  = trim((string)($post['position_other'] ?? ''));

    $department   = trim((string)($post['department'] ?? ''));
    $purpose      = $post['purpose'] ?? null;
    $studyCourse  = trim((string)($post['study_course'] ?? ''));
    $studyDept    = trim((string)($post['study_dept'] ?? ''));
    $electiveDept = trim((string)($post['elective_dept'] ?? ''));

    $checkIn  = toSqlDate($post['check_in_date']  ?? null);
    $checkOut = toSqlDate($post['check_out_date'] ?? null);

    $womanCount = isset($post['woman_count']) ? (int)$post['woman_count'] : 0;
    $manCount   = isset($post['man_count'])   ? (int)$post['man_count']   : 0;

    // validate
    if ($fullName === '') $errors[] = 'กรุณากรอกชื่อผู้จอง';
    if ($email === '')    $errors[] = 'กรุณากรอกอีเมล';

    if (!$checkIn || !$checkOut) {
        $errors[] = 'กรุณาระบุวันที่เข้าพักและวันที่ย้ายออกให้ถูกต้อง';
    } elseif ($checkOut < $checkIn) { // ใช้ได้ถ้าเป็น YYYY-MM-DD เสมอ
        $errors[] = 'วันที่ย้ายออกต้องไม่น้อยกว่าวันที่เข้าพัก';
    }

    if ($womanCount < 0 || $manCount < 0) {
        $errors[] = 'จำนวนผู้เข้าพักต้องไม่ติดลบ';
    }

    // normalize: student year / other position
    $studentYear = null;
    if ($position === 'student') {
        $studentYear = ($studentYearRaw === '' || $studentYearRaw === null)
            ? null
            : (int)$studentYearRaw;
    }

    if ($position !== 'other') {
        $positionOther = null;
    } else {
        if ($positionOther === '') $positionOther = null;
    }

    // guests parse
    $guests = parseGuestPost($post);

    if (!empty($errors)) {
        return [false, $errors, []];
    }

    $conn->begin_transaction();
    try {
        $ok = booking_updateByAdmin(
            $conn,
            $bookingId,
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
            $manCount
        );

        if (!$ok) {
            $conn->rollback();
            return [false, ['บันทึกไม่สำเร็จ (อัปเดต bookings)'], []];
        }

        // ถ้า repo นี้ไม่ throw เอง แนะนำให้มัน throw หรือคืน bool แล้วเช็ค
        guestRequest_replaceAll($conn, $bookingId, $guests);

        $conn->commit();
        return [true, [], $updated];

    } catch (Throwable $e) {
        $conn->rollback();
        return [false, ['บันทึกไม่สำเร็จ: ' . $e->getMessage()], []];
    }
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

        if(!booking_setApproved($conn, $bookingId)) {
            $conn->rollback();
            return null;
        }

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

        $conn->commit();

        return getBookingById($conn, $bookingId) ?: null;
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
    if (!booking_setRejected($conn, $bookingId, $reason)) return null;
    return getBookingById($conn, $bookingId);
}

/**
 * ตั้งสถานะกลับเป็น pending
 */
function setBookingPending(mysqli $conn, int $bookingId): bool
{
    return booking_setPending($conn, $bookingId);
}
