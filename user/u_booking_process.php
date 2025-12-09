<?php
require_once '../db.php';
require_once '../utils/booking_helper.php';
header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// ดัก error แบบรวม ๆ เผื่อหลุด try
function jsonError($message)
{
    echo json_encode([
        'status'  => 'error',
        'message' => $message
    ]);
    exit;
}

$fullName   = $_POST['fullName'] ?? '';
$phone      = $_POST['phone'] ?? '';
$lineId     = $_POST['lineId'] ?? '';
$email      = $_POST['email'] ?? '';
$position   = $_POST['position'] ?? null;
$studentYear = $_POST['studentYear'] ?? null;
$positionOther = $_POST['positionOtherDetail'] ?? null;
$department = $_POST['department'] ?? '';
$purpose    = $_POST['purpose'] ?? null;
$studyCourse = $_POST['studyCourse'] ?? '';
$studyDept  = $_POST['studyDept'] ?? '';
$electiveDept = $_POST['electiveDept'] ?? '';
$womanCount = isset($_POST['womanCount']) ? (int)$_POST['womanCount'] : 0;
$manCount   = isset($_POST['manCount'])   ? (int)$_POST['manCount']   : 0;
$adminUrl = 'http://localhost:3000/admin/ad_dashboard.php';

$dateStart = $_POST['checkInDate'] ?? null;
$dateEnd   = $_POST['checkOutDate'] ?? null;
$checkIn  = toSqlDate($dateStart);
$checkOut = toSqlDate($dateEnd);

try {
    //เพิ่มข้อมูล
    $sql = "INSERT INTO bookings
            (full_name, phone, line_id, email,
            position, student_year, position_other,
            department,
            purpose, study_course, study_dept, elective_dept,
            check_in_date, check_out_date,
            woman_count, man_count)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        jsonError('ไม่สามารถเตรียมคำสั่งฐานข้อมูลได้: ' . $conn->error);
    }

    $studentYear = ($studentYear === '') ? null : (int)$studentYear;

    $stmt->bind_param(
        'sssssissssssssii',
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

    if (!$stmt->execute()) {
        jsonError('ไม่สามารถดำเนินการคำสั่งฐานข้อมูลได้: ' . $stmt->error);
    }
    $stmt->close();

    $bookingId = $conn->insert_id;   // id ที่เพิ่ง insert
    if (!$bookingId) {
        jsonError('ไม่สามารถสร้างเลขที่ใบจองได้');
    }

    $bookingCode = formatBookingCode($bookingId, $checkIn);

    // ส่งอีเมลแจ้งเตือนถึงแอดมิน

    require '../PHPMailer/src/Exception.php';
    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';
    require '../mail_config.php';



    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        //คนส่งและผู้รับ
        $mail->setFrom('nareerats65@nu.ac.th', 'ระบบจองห้องพัก');
        $mail->addAddress('nareeerat28012547@gmail.com', 'Admin');

        //เนื้อหาอีเมล
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'มีคำขอจองห้องพักใหม่เข้ามา';
        $mail->Body    = '<div style="background:#f2f2f2; padding:20px; font-family:Kanit, sans-serif;">
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
                    <p style="margin:0;"><b>เลขที่ใบจอง #</b>' . $bookingCode . '</p>
                    <p style="margin:0;"><b>ชื่อผู้จอง :</b> ' . $fullName . '</p>
                    <p style="margin:0;"><b>หน่วยงานต้นสังกัด :</b> ' . $department . '</p>
                </div>

                <p><b>ช่วงที่ต้องการเข้าพัก:</b></p>
                <div style="background:#fafafa; border-left:4px solid #4e9bff; padding:12px; margin:12px 0;">
                    <p style="margin:0;"><b>วันที่เข้าพัก :</b> ' . $dateStart . '</p>
                    <p style="margin:0;"><b>วันที่ย้ายออก :</b> ' . $dateEnd . '</p>
                    <p style="margin:0;"><b>จำนวนผู้เข้าพัก :</b> หญิง ' . $womanCount . ' คน, ชาย ' . $manCount . ' คน</p>
                </div>

                <p style="margin-top:20px;">
                    กรุณาตรวจสอบรายละเอียดในระบบและดำเนินการอนุมัติค่ะ
                </p>

                <div style="text-align:center; margin:30px 0;">
                    <a href="' . $adminUrl . '" 
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
        // ✅ ทั้ง insert + mail สำเร็จ
        echo json_encode([
            'status'  => 'success',
            'bookingCode' => $bookingCode // อันนี้เพิ่มให้ส่งเลขที่ใบจองกลับไปฝั่งหน้าเว็บด้วย 
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status'  => 'success', // จะถือว่าส่งคำขอสำเร็จก็ได้
            'mailError' => 'ส่งอีเมลแจ้งเตือนไม่สำเร็จ: ' . $mail->ErrorInfo
        ]);
    }
} catch (Throwable $e) {
    jsonError('เกิดข้อผิดพลาดในระบบ: ' . $e->getMessage());
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
