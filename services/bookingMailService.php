<?php
// services/bookingMailService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__, 1) . '/config.php';
require_once UTILS_PATH . '/booking_helper.php';
require_once CONFIG_PATH . '/mail_config.php';

// โหลด PHPMailer
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

/**
 * ตั้งค่าตัว PHPMailer พื้นฐาน ตามค่าใน mail_config.php
 */
function createBaseMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    // ตั้งค่า SMTP ตาม config กลาง
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    return $mail;
}

/**
 * 1) ส่งอีเมลแจ้งเตือน "มีคำขอจองห้องพักใหม่" ไปยัง Admin
 *
 * ใช้ตอนผู้ใช้กดส่งคำขอจองครั้งแรก
 */
function sendNewBooking(
    string $bookingCode,
    string $fullName,
    string $department,
    string $dateStart,
    string $dateEnd,
    int $womanCount,
    int $manCount,
    string $adminUrl
): array {
    $mail = createBaseMailer();

    try {
        // From / To
        $mail->setFrom('nareerats65@nu.ac.th', 'ระบบจองห้องพัก'); // ตั้งใน mail_config.php
        $mail->addAddress('nareeerat28012547@gmail.com', 'Admin');    // ตั้งเมล admin ใน mail_config.php

        // เนื้อหาอีเมล
        $mail->isHTML(true);
        $mail->Subject = 'มีคำขอจองห้องพักใหม่เข้ามา';

        $mail->Body = '
        <div style="background:#f2f2f2; padding:20px; font-family:Kanit, sans-serif;">
            <div style="max-width:600px; margin:auto; background:white; border-radius:12px; 
                        overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">

                <div style="background:#F57B39; padding:18px; align-items:center; color:#ffffff; display:flex;">
                    <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" width="60" height="60" class="me-3">
                    <div>
                        <h2 style="margin:0; font-size:22px;">แจ้งเตือนคำขอจองห้องพักใหม่</h2>
                    </div>
                </div>

                <div style="padding:20px; color:#333; line-height:1.7;">
                    <p>เรียนเจ้าหน้าที่,</p>

                    <p>มีคำขอจองห้องพักใหม่จาก:</p>

                    <div style="background:#fafafa; border-left:4px solid #F57B39; padding:12px; margin:12px 0;">
                        <p style="margin:0;"><b>เลขที่ใบจอง #</b>' . htmlspecialchars($bookingCode, ENT_QUOTES, 'UTF-8') . '</p>
                        <p style="margin:0;"><b>ชื่อผู้จอง :</b> ' . htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . '</p>
                        <p style="margin:0;"><b>หน่วยงานต้นสังกัด :</b> ' . htmlspecialchars($department, ENT_QUOTES, 'UTF-8') . '</p>
                    </div>

                    <p><b>ช่วงที่ต้องการเข้าพัก:</b></p>
                    <div style="background:#fafafa; border-left:4px solid #4e9bff; padding:12px; margin:12px 0;">
                        <p style="margin:0;"><b>วันที่เข้าพัก :</b> ' . htmlspecialchars($dateStart, ENT_QUOTES, 'UTF-8') . '</p>
                        <p style="margin:0;"><b>วันที่ย้ายออก :</b> ' . htmlspecialchars($dateEnd,   ENT_QUOTES, 'UTF-8') . '</p>
                        <p style="margin:0;"><b>จำนวนผู้เข้าพัก :</b> หญิง ' . (int)$womanCount . ' คน, ชาย ' . (int)$manCount . ' คน</p>
                    </div>

                    <p style="margin-top:20px;">
                        กรุณาตรวจสอบรายละเอียดในระบบและดำเนินการอนุมัติค่ะ
                    </p>

                    <div style="text-align:center; margin:30px 0;">
                        <a href="' . htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8') . '" 
                        style="background:#F57B39; color:white; padding:12px 25px;
                                border-radius:8px; text-decoration:none; font-weight:bold;">
                            เปิดคำขอในระบบ
                        </a>
                    </div>

                    <hr style="border:none; border-top:1px solid #ddd; margin:25px 0;">

                    <p style="font-size:14px; color:#777; text-align:center;">
                        อีเมลฉบับนี้เป็นการแจ้งอัตโนมัติจากระบบจองห้องพัก
                    </p>
                </div>
            </div>
        </div>';

        $mail->send();

        return [
            'success' => true,
            'error'   => null,
        ];
    } catch (Exception $e) {
        error_log('Mail error (new booking admin): ' . $mail->ErrorInfo);
        return [
            'success' => false,
            'error'   => 'ส่งอีเมลแจ้งเตือนไม่สำเร็จ: ' . $mail->ErrorInfo,
        ];
    }
}

/**
 * 2) ส่งอีเมลผลการพิจารณาการจองให้ "ผู้จอง"
 *    - $status = 'approved'  → เมลอนุมัติ + ลิงก์กรอกผู้เข้าพัก / อัปโหลดเอกสาร
 *    - $status = 'rejected'  → เมลแจ้งไม่อนุมัติ + เหตุผล
 */
function sendBookingResult(array $booking, string $status, ?string $reason = null): void
{
    $fullName = $booking['full_name']      ?? '';
    $email    = $booking['email']          ?? '';
    $checkIn  = $booking['check_in_date']  ?? null;
    $checkOut = $booking['check_out_date'] ?? null;
    $w        = (int)($booking['woman_count'] ?? 0);
    $m        = (int)($booking['man_count']   ?? 0);
    $id       = (int)($booking['id'] ?? 0);

    if (!$email) {
        return;
    }

    // format วันที่ + เลขที่ใบจอง
    $checkIn     = $checkIn  ? formatDate($checkIn)   : null;
    $checkOut    = $checkOut ? formatDate($checkOut)  : null;
    $bookingCode = formatBookingCode($id);

    $mail = createBaseMailer();

    try {
        $mail->setFrom('nareerats65@nu.ac.th', 'ระบบจองห้องพัก');
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);

        if ($status === 'approved') {
            $linkUpload = 'http://localhost:3000/user/u_upload_document.php?booking_id=' . $id;

            global $conn;
            $roomSummaryHtml = buildRoomSummaryHtml($conn, $id);
            

            $mail->Subject = 'ผลการจองห้องพัก: อนุมัติ';

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
                                หากไม่ถูกต้องหรือมีปัญหาโปรดติดต่อ 080-000-0000
                            </p>

                            <div style="background:#fafafa; border-radius:8px; padding:12px 14px;
                                border-left:4px solid #F57B39; margin:10px 0 18px;">
                                <p style="margin:0;"><b>เลขที่ใบจอง #</b>' . $bookingCode . '</p>
                                <p style="margin:0;"><b>ชื่อผู้จอง:</b> ' . htmlspecialchars($fullName, ENT_QUOTES, "UTF-8") . '</p>
                                <p style="margin:0;"><b>วันที่เข้าพัก:</b> ' . htmlspecialchars($checkIn  ?? '', ENT_QUOTES, "UTF-8") . '</p>
                                <p style="margin:0;"><b>วันที่ย้ายออก:</b> ' . htmlspecialchars($checkOut ?? '', ENT_QUOTES, "UTF-8") . '</p>
                                <p style="margin:0;"><b>จำนวนผู้เข้าพัก:</b> หญิง ' . $w . ' คน, ชาย ' . $m . ' คน</p>
                            </div>
                            ' . $roomSummaryHtml . '
                            <p>
                                สามรถอัปโหลดเอกสารเพิ่มเติมได้ด้านล่างนี้
                            </p>

                            <div style="text-align:center; margin:24px 0 10px;">
                                <a href="' . $linkUpload . '" style="background:#F5F5F5; color:#333333; padding:10px 22px;
                                    border-radius:999px; text-decoration:none; font-weight:bold;
                                    display:inline-block; font-size:14px;">
                                    อัปโหลดเอกสารเพิ่มเติม
                                </a>
                            </div>

                            <p style="font-size:13px; color:#777; margin-top:15px;">
                                หากกดปุ่มไม่ได้ สามารถคัดลอกลิงก์ด้านล่างไปวางในเบราว์เซอร์ได้เช่นกัน:<br>
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

            $mail->Subject = 'ผลการจองห้องพัก: อนุมัติ';
            $mail->Body    = $body;
        } elseif ($status === 'rejected') {
            $mail->Subject = 'ผลการจองห้องพัก: ไม่อนุมัติ';

            $body = '<div style="background:#f2f2f2; padding:20px; font-family:Kanit, sans-serif;">
                        <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:12px;
                                overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                            <div style="background:#F57B39; padding:18px; align-items:center; color:#ffffff; display:flex;">
                                <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" width="60" height="60" class="me-3">
                                <div>
                                    <h2 style="margin:0; font-size:22px;">ผลการจองห้องพักของคุณ</h2>
                                    <p style="margin:4px 0 0; font-size:14px; opacity:.9;">
                                        คำขอของคุณ <b>ไม่ได้รับการอนุมัติ</b> 
                                    </p>
                                </div>
                            </div>

                            <div style="padding:20px; color:#333333; line-height:1.7;">
                                <p>เรียนคุณ <b>' . htmlspecialchars($fullName, ENT_QUOTES, "UTF-8") . '</b>,</p>

                                <p>
                                    คำขอจองห้องพักของคุณไม่ได้รับการอนุมัติ
                                    เนื่องจาก <b>' . htmlspecialchars((string)$reason, ENT_QUOTES, "UTF-8") . '</b>.<br>
                                    หากมีข้อสงสัย กรุณาติดต่อเจ้าหน้าที่เพื่อสอบถามข้อมูลเพิ่มเติม
                                </p>

                                <div style="background:#fafafa; border-radius:8px; padding:12px 14px;
                                    border-left:4px solid #F57B39; margin:10px 0 18px;">
                                    <p style="margin:0;"><b>หน่วยงาน :</b> หน่วยงานกิจการนิสิต คณะแพทยศาสตร์ มหาวิทยาลัยนเรศวร</p>
                                    <p style="margin:0;"><b>เบอร์โทรศัพท์ :</b> 082-7946535 </p>
                                    <p style="margin:0;"><b>E-mail :</b> dormitory@nu.ac.th</p>
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
            // status อื่นยังไม่รองรับ
            return;
        }

        $mail->send();
    } catch (Exception $e) {
        error_log('Mail error (booking result): ' . $mail->ErrorInfo);
    }
}
