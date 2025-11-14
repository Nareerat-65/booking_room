<?php
// ad_updateStatus.php
header('Content-Type: text/plain; charset=utf-8');

require_once '../db.php';  // ไฟล์เชื่อมต่อ $conn

// ====== PHPMailer ======
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 👉 ปรับ path ให้ตรงกับโฟลเดอร์ PHPMailer ของโปรเจกต์เธอ
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

/**
 * ฟังก์ชันจัดสรรห้องตาม booking
 */
function allocateRooms(mysqli $conn, int $bookingId): void
{
    // ถ้ามี allocation อยู่แล้ว ไม่ทำซ้ำ (กันอนุมัติซ้ำ)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM room_allocations WHERE booking_id = ?");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $stmt->bind_result($exists);
    $stmt->fetch();
    $stmt->close();

    if ($exists > 0) {
        return;
    }

    // ดึงข้อมูล booking
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
        return;
    }
    $stmt->close();

    // -------------------------------
    // กำหนดช่วงวันที่ของการจองนี้
    // -------------------------------
    $startDate = $checkIn;
    $endDate   = $checkOut;

    // -------------------------------
    // ดึง "ห้องที่ยังว่าง" ในช่วงนี้
    //   - ห้องไม่ว่าง = มี room_allocations ที่ช่วงวันทับซ้อนกัน
    //   - เราให้ถือว่าห้องเดิมต้องว่างหลังจาก end_date + 3 วัน (ทำความสะอาด)
    // -------------------------------
    $rooms = [];

    $sqlRooms = "
        SELECT r.id, r.capacity
        FROM rooms r
        WHERE r.id NOT IN (
            SELECT DISTINCT ra.room_id
            FROM room_allocations ra
            WHERE NOT (
                DATE_ADD(ra.end_date, INTERVAL 3 DAY) <= ?
                OR ra.start_date >= ?
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

    // ถ้าไม่มีห้องว่างเลย ก็ออกไป (จะเลือกไป up สถานะ booking เพิ่มทีหลังก็ได้)
    if (empty($rooms)) {
        return;
    }

    $roomIndex = 0;

    $insert = $conn->prepare("
        INSERT INTO room_allocations
            (booking_id, room_id, start_date, end_date, woman_count, man_count)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // 1) จัดผู้หญิงก่อน (ห้องหนึ่งไม่เกิน capacity และแยกเพศ)
    $remainW = (int)$womanCount;
    while ($remainW > 0 && $roomIndex < count($rooms)) {
        $roomId = (int)$rooms[$roomIndex]['id'];
        $cap    = (int)$rooms[$roomIndex]['capacity'];
        $num    = min($cap, $remainW);   // จำนวนคนลงห้องนี้

        $zero = 0;
        $insert->bind_param(
            'iissii',
            $bookingId,
            $roomId,
            $startDate,
            $endDate,
            $num,   // woman_count
            $zero   // man_count
        );
        $insert->execute();

        $remainW   -= $num;
        $roomIndex += 1;
    }

    // 2) จัดผู้ชายต่อ (ใช้ห้องถัดไปเสมอ → ไม่ปนห้องกับผู้หญิง)
    $remainM = (int)$manCount;
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
            $zero,  // woman_count
            $num    // man_count
        );
        $insert->execute();

        $remainM   -= $num;
        $roomIndex += 1;
    }

    $insert->close();
}

/**
 * สร้าง token แบบสุ่ม เอาไว้ใส่ในลิงก์อีเมล
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * ส่งเมลแจ้งผลการจอง
 * $status: 'approved' หรือ 'rejected'
 */
function sendBookingEmail(mysqli $conn, int $bookingId, string $status, ?string $reason = null): void
{
    // ดึงข้อมูลการจอง
    $stmt = $conn->prepare("
        SELECT full_name, email, check_in_date, check_out_date,
               woman_count, man_count, confirm_token
        FROM bookings
        WHERE id = ?
    ");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $stmt->bind_result($fullName, $email, $checkIn, $checkOut, $w, $m, $token);
    if (!$stmt->fetch()) {
        $stmt->close();
        return;
    }
    $stmt->close();

    $mail = new PHPMailer(true);

    try {
        // ===== ตั้งค่า SMTP สำหรับ Gmail =====
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';    // Gmail SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nareerats65@nu.ac.th';      // 👉 Gmail ของเธอ
        $mail->Password   = 'gwfq rtik mszl bjhl';       // 👉 App Password (อย่าใช้รหัสจริง)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->CharSet = 'UTF-8';

        // ผู้ส่ง / ผู้รับ
        $mail->setFrom('nareerats65@nu.ac.th', 'ระบบจองห้องพัก');
        $mail->addAddress($email, $fullName);

        $mail->isHTML(false); // ส่งเป็น text ธรรมดา
        $subject = '';
        $body    = '';

        if ($status === 'approved') {
            // ลิงก์ให้ผู้จองเข้าไปกรอกรายชื่อผู้เข้าพัก

            $link = 'http://localhost:3000/user/guest_fill.php?token=' . urlencode((string)$token);

            $subject = 'ผลการจองห้องพัก: อนุมัติ';

            $body  = "สวัสดีคุณ {$fullName}\n\n";
            $body .= "คำขอจองห้องพักของคุณได้รับการ \"อนุมัติ\" แล้ว\n\n";
            $body .= "ช่วงวันที่เข้าพัก: {$checkIn} ถึง {$checkOut}\n";
            $body .= "จำนวนผู้เข้าพัก: หญิง {$w} คน ชาย {$m} คน\n\n";
            $body .= "กรุณาคลิกลิงก์ด้านล่างเพื่อกรอกรายชื่อผู้เข้าพักในแต่ละห้อง:\n";
            $body .= "{$link}\n\n";
            $body .= "ขอบคุณค่ะ";
        } elseif ($status === 'rejected') {
            $subject = 'ผลการจองห้องพัก: ไม่อนุมัติ';

            $body  = "สวัสดีคุณ {$fullName}\n\n";
            $body .= "คำขอจองห้องพักของคุณ \"ไม่อนุมัติ\" ค่ะ\n\n";
            if ($reason) {
                $body .= "เหตุผล: {$reason}\n\n";
            }
            $body .= "หากมีข้อสงสัยสามารถติดต่อเจ้าหน้าที่ได้ค่ะ";
        } else {
            return;
        }

        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        // ไม่ต้อง echo ให้ user เห็น แค่ log ไว้พอ
        error_log('Mail error: ' . $mail->ErrorInfo);
    }
}

// ---------- รับค่าจาก Ajax ----------
$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = $_POST['status'] ?? '';
$reason = $_POST['reason'] ?? null;

if (!$id || !in_array($status, ['approved', 'rejected', 'pending'], true)) {
    http_response_code(400);
    echo 'invalid';
    exit;
}

if ($status === 'approved') {

    // 1) สร้าง token + กำหนดวันหมดอายุ (เช่น 7 วัน)
    $token  = generateToken();
    $expire = date('Y-m-d H:i:s', strtotime('+7 days'));

    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'approved',
            reject_reason = NULL,
            confirm_token = ?,
            confirm_token_expires = ?
        WHERE id = ?
    ");
    $stmt->bind_param('ssi', $token, $expire, $id);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        // จัดสรรห้องพักตาม booking นี้
        allocateRooms($conn, $id);

        // ส่งเมลแจ้งผล (แบบอนุมัติ + มีลิงก์กรอกชื่อ)
        sendBookingEmail($conn, $id, 'approved', null);

        echo 'success';
    } else {
        echo 'error';
    }
} elseif ($status === 'rejected') {

    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'rejected', reject_reason = ?
        WHERE id = ?
    ");
    $stmt->bind_param('si', $reason, $id);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        // ส่งเมลแจ้งว่าไม่อนุมัติ
        sendBookingEmail($conn, $id, 'rejected', $reason);
    }

    echo $ok ? 'success' : 'error';
} else {
    // reset เป็น pending (ถ้ามีในอนาคต)
    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'pending', reject_reason = NULL
        WHERE id = ?
    ");
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();

    echo $ok ? 'success' : 'error';
}

$conn->close();
