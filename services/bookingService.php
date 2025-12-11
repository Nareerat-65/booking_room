<?php
// services/bookingService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../utils/booking_helper.php';

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

/**
 * อัปเดตข้อมูลการจองจากฟอร์ม admin
 * return [bool $ok, array $errors, array $updatedFields]
 */
function updateBookingFromAdminForm(mysqli $conn, int $bookingId, array $post): array
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
 * จัดห้องแบบ strict: ถ้าห้องไม่พอ → return false
 * (ต้องเรียกภายใต้ transaction)
 */
function allocateRoomsStrict(mysqli $conn, int $bookingId): bool
{
    $stmt = $conn->prepare("
        SELECT woman_count, man_count, check_in_date, check_out_date
        FROM bookings
        WHERE id = ?
    ");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $stmt->bind_result($womanCount, $manCount, $checkIn, $checkOut);
    if (!$stmt->fetch()) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    $startDate = $checkIn;
    $endDate   = $checkOut;
    $rooms     = [];

    $sqlRooms = "
        SELECT r.id, r.capacity
        FROM rooms r
        WHERE r.id NOT IN (
            SELECT DISTINCT ra.room_id
            FROM room_allocations ra
            WHERE NOT (
                DATE_ADD(ra.end_date, INTERVAL 3 DAY) < ?
                OR ra.start_date > ?
            )
        )
        ORDER BY r.id ASC
    ";

    $stmt = $conn->prepare($sqlRooms);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $rooms[] = $row;
    }
    $stmt->close();

    if (empty($rooms)) {
        return false;
    }

    $roomIndex = 0;
    $insert = $conn->prepare("
        INSERT INTO room_allocations
            (booking_id, room_id, start_date, end_date, woman_count, man_count)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $remainW = (int)$womanCount;
    $remainM = (int)$manCount;

    // ผู้หญิง
    while ($remainW > 0 && $roomIndex < count($rooms)) {
        $roomId = (int)$rooms[$roomIndex]['id'];
        $cap    = (int)$rooms[$roomIndex]['capacity'];
        $num    = min($cap, $remainW);

        $zero = 0;
        $insert->bind_param(
            'iissii',
            $bookingId,
            $roomId,
            $startDate,
            $endDate,
            $num,
            $zero
        );
        $insert->execute();

        $remainW   -= $num;
        $roomIndex += 1;
    }

    // ผู้ชาย
    while ($remainM > 0 && $roomIndex < count($rooms)) {
        $roomId = (int)$rooms[$roomIndex]['id'];
        $cap    = (int)$rooms[$roomIndex]['capacity'];
        $num    = min($cap, $remainM);

        $zero = 0;
        $insert->bind_param(
            'iissii',
            $bookingId,
            $roomId,
            $startDate,
            $endDate,
            $zero,
            $num
        );
        $insert->execute();

        $remainM   -= $num;
        $roomIndex += 1;
    }

    $insert->close();

    return $remainW === 0 && $remainM === 0;
}

/**
 * ลบ booking + room_allocations + booking_documents (และไฟล์)
 */
function deleteBookingWithRelations(mysqli $conn, int $bookingId): bool
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
    // สร้าง token
    $token  = bin2hex(random_bytes(16)); // 32 chars
    $expire = date('Y-m-d H:i:s', strtotime('+7 days'));

    try {
        $conn->begin_transaction();

        // อัปเดต booking
        $stmt = $conn->prepare("
            UPDATE bookings
            SET status = 'approved',
                reject_reason = NULL,
                confirm_token = ?,
                confirm_token_expires = ?
            WHERE id = ?
        ");
        $stmt->bind_param('ssi', $token, $expire, $bookingId);
        if (!$stmt->execute()) {
            $stmt->close();
            $conn->rollback();
            return null;
        }
        $stmt->close();

        // ลบ allocations เดิม (กันกรณีมีอยู่แล้ว)
        $stmt = $conn->prepare("DELETE FROM room_allocations WHERE booking_id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $stmt->close();

        // จัดห้องใหม่
        $okAlloc = allocateRoomsStrict($conn, $bookingId);
        if (!$okAlloc) {
            $conn->rollback();
            return null;
        }

        $conn->commit();

        // ดึงข้อมูล booking ที่ต้องใช้ส่งเมล
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

/**
 * ส่งอีเมลแจ้งผล (approved / rejected)
 * ใช้ข้อมูลจาก booking array
 */
function sendBookingResultEmail(array $booking, string $status, ?string $reason = null): void
{
    $fullName = $booking['full_name'] ?? '';
    $email    = $booking['email']     ?? '';
    $checkIn  = $booking['check_in_date']  ?? null;
    $checkOut = $booking['check_out_date'] ?? null;
    $w        = (int)($booking['woman_count'] ?? 0);
    $m        = (int)($booking['man_count']   ?? 0);
    $token    = $booking['confirm_token']     ?? null;
    $id       = (int)($booking['id'] ?? 0);

    if (!$email) {
        return;
    }

    // ใช้ helper เดิม
    $checkIn  = formatDate($checkIn);   // ถ้ายังไม่มีให้ใช้ toSqlDate แล้ว format เอง
    $checkOut = formatDate($checkOut);
    $bookingCode = formatBookingCode($id);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nareerats65@nu.ac.th';
        $mail->Password   = 'gwfq rtik mszl bjhl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('nareerats65@nu.ac.th', 'ระบบจองห้องพัก');
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Encoding = 'base64';

        if ($status === 'approved') {
            $linkGuest  = 'http://localhost:3000/user/u_guest_form.php?token=' . urlencode((string)$token);
            $linkUpload = 'http://localhost:3000/user/u_upload_document.php?booking_id=' . $id;

            $mail->Subject = 'ผลการจองห้องพัก: อนุมัติ';

            // เอา body เดิมของคุณมาใส่ได้เลย (ผมไม่แปะซ้ำทั้งก้อนเพื่อไม่ให้ยาวเกิน)
            $body = '<div style="background:#f2f2f2; padding:20px; font-family:Kanit, sans-serif;">
                    <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:12px;
                            overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                        <div style="background:#F57B39; padding:18px; align-items:center; color:#ffffff; display:flex;">
                            <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" width="60" height="60" class="me-3">
                            <div>
                                <h2 style="margin:0; font-size:22px;">ยืนยันการจองห้องพักของคุณ</h2>
                                <p style="margin:4px 0 0; font-size:14px; opacity:.9;">
                                    คำขอของคุณได้รับการ <b>อนุมัติ</b> แล้ว
                                </p>
                            </div>
                        </div>

                        <div style="padding:20px; color:#333333; line-height:1.7;">
                            <p>เรียนคุณ <b>' . htmlspecialchars($fullName, ENT_QUOTES, "UTF-8") . '</b>,</p>

                            <p>
                                ระบบได้อนุมัติคำขอจองห้องพักของคุณเรียบร้อยแล้ว
                                โปรดตรวจสอบข้อมูลรายละเอียดการเข้าพักด้านล่าง
                                และกดปุ่มเพื่อกรอกรายชื่อผู้เข้าพักในแต่ละห้อง
                            </p>

                            <div style="background:#fafafa; border-radius:8px; padding:12px 14px;
                                border-left:4px solid #F57B39; margin:10px 0 18px;">
                                <p style="margin:0;"><b>เลขที่ใบจอง #</b>' . $bookingCode . '</p>
                                <p style="margin:0;"><b>ชื่อผู้จอง:</b> ' . htmlspecialchars($fullName, ENT_QUOTES, "UTF-8") . '</p>
                                <p style="margin:0;"><b>วันที่เข้าพัก:</b> ' . htmlspecialchars($checkIn  ?? '', ENT_QUOTES, 'UTF-8') . '</p>
                                <p style="margin:0;"><b>วันที่ย้ายออก:</b> ' . htmlspecialchars($checkOut ?? '', ENT_QUOTES, "UTF-8") . '</p>
                                <p style="margin:0;"><b>จำนวนผู้เข้าพัก:</b> หญิง ' . $w . ' คน, ชาย ' . $m . ' คน</p>
                            </div>
            
                            <p>
                                <b>ขั้นตอนถัดไป:</b><br>
                                กรุณากดปุ่มด้านล่างเพื่อกรอกรายชื่อผู้เข้าพัก (พร้อมเบอร์โทร) แยกตามแต่ละห้อง
                            </p>

                            <div style="text-align:center; margin:24px 0 10px;">
                                <a href="' . $linkGuest . '" style="background:#F57B39; color:#ffffff; padding:12px 26px; 
                                border-radius:999px; text-decoration:none; font-weight:bold;
                                display:inline-block;">
                                    กรอกรายชื่อผู้เข้าพัก
                                </a>
                                 <!-- ปุ่มอัปโหลดเอกสาร -->
                                <a href="' . $linkUpload . '" style="background:#F5F5F5; color:#333333; padding:10px 22px;
                                    border-radius:999px; text-decoration:none; font-weight:bold;
                                    display:inline-block; font-size:14px;">
                                    อัปโหลดเอกสารเพิ่มเติม
                                </a>
                            </div>

                            <p style="font-size:13px; color:#777; margin-top:15px;">
                                หากกดปุ่มไม่ได้ สามารถคัดลอกลิงก์ด้านล่างไปวางในเบราว์เซอร์ได้เช่นกัน:<br>
                                <b>กรอกรายชื่อผู้เข้าพัก:</b><br>
                                <span style="word-break:break-all; color:#555;">
                                    ' . $linkGuest . '
                                </span>
                                <b>อัปโหลดเอกสารประกอบ:</b><br>
                                <span style="word-break:break-all; color:#555;">
                                    ' . $linkUpload . '
                                </span>
                            </p>

                            <hr style="border:none; border-top:1px solid #e0e0e0; margin:22px 0 12px;">

                            <p style="font-size:12px; color:#999; text-align:center; margin:0;">
                                อีเมลฉบับนี้ถูกส่งจากระบบจองห้องพักโดยอัตโนมัติ<br>
                                กรุณาอย่าตอบกลับอีเมลฉบับนี้
                            </p>
                        </div>
                    </div>
                </div>';

            $mail->Body = $body;

        } elseif ($status === 'rejected') {
            $mail->Subject = 'ผลการจองห้องพัก: ไม่อนุมัติ';
            $body = '<div style="background:#f2f2f2; padding:20px; font-family:Kanit, sans-serif;">
                        <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:12px;
                                overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                            <div style="background:#F57B39; padding:18px; align-items:center; color:#ffffff; display:flex;">
                                <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" width="60" height="60" class="me-3">
                                <div>
                                    <h2 style="margin:0; font-size:22px;">ยืนยันการจองห้องพักของคุณ</h2>
                                    <p style="margin:4px 0 0; font-size:14px; opacity:.9;">
                                        คำขอของคุณ <b>ไม่ได้รับการอนุมัติ</b> 
                                    </p>
                                </div>
                            </div>

                            <div style="padding:20px; color:#333333; line-height:1.7;">
                                <p>เรียนคุณ <b>' . htmlspecialchars($fullName, ENT_QUOTES, "UTF-8") . '</b>,</p>

                                <p>
                                    คำขอจองห้องพักของคุณไม่ได้รับการอนุมัติ
                                    เนื่องจาก <b>' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '</b>.
                                    หากมีข้อสงสัย กรุณาติดต่อเจ้าหน้าที่เพื่อสอบถามข้อมูลเพิ่มเติม
                                </p>

                                <div style="background:#fafafa; border-radius:8px; padding:12px 14px;
                                    border-left:4px solid #F57B39; margin:10px 0 18px;">
                                    <p style="margin:0;"><b>หน่วยงาน : </b> หน่วยงานกิจการนิสิต คณะแพทยศาสตร์ มหาวิทยาลัยนเรศวร </p>
                                    <p style="margin:0;"><b>เบอร์โทรศัพท์ : </b> 0-5596-7847 </p>
                                    <p style="margin:0;"><b>E-mail : </b> dormitory@nu.ac.th </p>
                                    
                                </div>

                                <hr style="border:none; border-top:1px solid #e0e0e0; margin:22px 0 12px;">

                                <p style="font-size:12px; color:#999; text-align:center; margin:0;">
                                    อีเมลฉบับนี้ถูกส่งจากระบบจองห้องพักโดยอัตโนมัติ<br>
                                    กรุณาอย่าตอบกลับอีเมลฉบับนี้
                                </p>
                            </div>
                        </div>
                    </div>';
            $mail->Body = $body;
        } else {
            return;
        }

        $mail->send();
    } catch (Exception $e) {
        error_log('Mail error: ' . $mail->ErrorInfo);
    }
}
