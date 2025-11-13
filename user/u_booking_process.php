<?php
require_once '../db.php';

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

//แปลงวันที่จากรูปแบบ d-m-Y หรือ Y-m-d เป็น Y-m-d
function toSqlDate($d) {
    if (!$d) return null;
    $dt = DateTime::createFromFormat('d-m-Y', $d) ?: DateTime::createFromFormat('Y-m-d', $d);
    return $dt ? $dt->format('Y-m-d') : null;
}
$checkInDate  = toSqlDate($_POST['checkInDate'] ?? null);
$checkOutDate = toSqlDate($_POST['checkOutDate'] ?? null);

$womanCount = isset($_POST['womanCount']) ? (int)$_POST['womanCount'] : 0;
$manCount   = isset($_POST['manCount'])   ? (int)$_POST['manCount']   : 0;

// ตรวจสอบเบื้องต้นฝั่ง server (กันคนปิด JavaScript แล้วยิงตรง)
$errors = [];

if ($fullName === '')  $errors[] = 'กรุณากรอกชื่อ–นามสกุล';
if ($phone === '')     $errors[] = 'กรุณากรอกเบอร์โทรศัพท์';
if ($position === null) $errors[] = 'กรุณาเลือกตำแหน่ง';
if ($purpose === null) $errors[] = 'กรุณาเลือกวัตถุประสงค์การเข้าพัก';
if ($checkInDate === null || $checkOutDate === null) {
    $errors[] = 'กรุณาเลือกวันที่ย้ายเข้าและวันที่ย้ายออก';
} elseif ($checkOutDate < $checkInDate) {
    $errors[] = 'วันที่ย้ายออกต้องไม่ก่อนวันที่ย้ายเข้าพัก';
}

// ถ้ามี error แสดงข้อความแล้วหยุด
if (!empty($errors)) {
    echo '<h3>พบข้อผิดพลาด</h3><ul>';
    foreach ($errors as $e) {
        echo '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul><p><a href="javascript:history.back()">← กลับไปแก้ไข</a></p>';
    exit;
}

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
    die('Prepare failed: ' . $conn->error);
}

// แปลง student_year ถ้าเป็นค่าว่างให้เป็น null
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
    $checkInDate,
    $checkOutDate,
    $womanCount,
    $manCount
);
if (!$stmt->execute()) {
    die('Execute failed: ' . $stmt->error);
}

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';

// // สร้าง object
// $mail = new PHPMailer(true);

// try {
//     // ตั้งค่า SMTP
//     $mail->isSMTP();
//     $mail->Host = 'smtp.gmail.com';  // ใช้ Gmail SMTP
//     $mail->SMTPAuth = true;
//     $mail->Username = 'nareerats65@nu.ac.th';     // 👉 Gmail ของคุณ
//     $mail->Password = 'gwfq rtik mszl bjhl';       // 👉 รหัสผ่านแอป (App Password)
//     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//     $mail->Port = 587;

//     // ผู้ส่ง
//     $mail->setFrom('nareerats65@nu.ac.th', 'ระบบจองห้องพัก');
//     // ผู้รับ (Admin)
//     $mail->addAddress('nareeerat28012547@gmail.com', 'Admin');

//     // เนื้อหาอีเมล
//     $mail->isHTML(true);
//     $mail->CharSet = 'UTF-8';  
//     $mail->Subject = 'มีคำขอจองห้องพักใหม่เข้ามา';
//     $mail->Body    = "
//         <h3>มีคำขอจองห้องพักใหม่</h3>
//         <p><b>ชื่อผู้จอง:</b> {$fullName}</p>
//         <p><b>เบอร์โทร:</b> {$phone}</p>
//         <p><b>LINE ID:</b> {$lineId}</p>
//         <p><b>Email:</b> {$email}</p>
//         <p><b>หน่วยงาน:</b> {$department}</p>
//         <p><b>วันที่เข้าพัก:</b> {$checkInDate}</p>
//         <p><b>วันที่ย้ายออก:</b> {$checkOutDate}</p>
//     ";

//     $mail->send();
//     echo "OK";
// } catch (Exception $e) {
//     echo "MAIL ERROR: " . $mail->ErrorInfo;
// }
echo "OK";

$stmt->close();
$conn->close();
