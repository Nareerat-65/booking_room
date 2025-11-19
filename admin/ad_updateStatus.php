<?php
header('Content-Type: text/plain; charset=utf-8');

require_once '../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

/**
 * จัดสรรห้องพักให้ booking ที่อนุมัติแล้ว
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

        $mail->isHTML(true); 
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $subject = '';
        $body    = '';

        if ($status === 'approved') {
            // ลิงก์ให้ผู้จองเข้าไปกรอกรายชื่อผู้เข้าพัก

            $link = 'http://localhost:3000/user/u_guest_form.php?token=' . urlencode((string)$token);

            $subject = 'ผลการจองห้องพัก: อนุมัติ';

            $body  = '<div style="background:#f2f2f2; padding:20px; font-family:Kanit, sans-serif;">
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
                                <a href="' . $link . '" style="background:#F57B39; color:#ffffff; padding:12px 26px; 
                                border-radius:999px; text-decoration:none; font-weight:bold;
                                display:inline-block;">
                                    กรอกรายชื่อผู้เข้าพัก
                                </a>
                            </div>

                            <p style="font-size:13px; color:#777; margin-top:15px;">
                                หากกดปุ่มไม่ได้ สามารถคัดลอกลิงก์ด้านล่างไปวางในเบราว์เซอร์ได้เช่นกัน:<br>
                                <span style="word-break:break-all; color:#555;">
                                    ' . $link . '
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
        } elseif ($status === 'rejected') {
            $subject = 'ผลการจองห้องพัก: ไม่อนุมัติ';

            $body  = '<div style="background:#f2f2f2; padding:20px; font-family:Kanit, sans-serif;">
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
                                    เนื่องจาก <b>' . $reason . '</b>.
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
