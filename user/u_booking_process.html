
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db.php';
require_once '../utils/booking_helper.php';
require_once '../services/bookingMailService.php';
header('Content-Type: application/json; charset=utf-8');

// ดัก error แบบรวม ๆ เผื่อหลุด try
function jsonError($message)
{
    echo json_encode([
        'status'  => 'error',
        'message' => $message
    ]);
    exit;
}

$fullName      = $_POST['fullName'] ?? '';
$phone         = $_POST['phone'] ?? '';
$lineId        = $_POST['lineId'] ?? '';
$email         = $_POST['email'] ?? '';
$position      = $_POST['position'] ?? null;
$studentYear   = $_POST['studentYear'] ?? null;
$positionOther = $_POST['positionOtherDetail'] ?? null;
$department    = $_POST['department'] ?? '';
$purpose       = $_POST['purpose'] ?? null;
$studyCourse   = $_POST['studyCourse'] ?? '';
$studyDept     = $_POST['studyDept'] ?? '';
$electiveDept  = $_POST['electiveDept'] ?? '';
$womanCount    = isset($_POST['womanCount']) ? (int)$_POST['womanCount'] : 0;
$manCount      = isset($_POST['manCount'])   ? (int)$_POST['manCount']   : 0;

$adminUrl = 'http://localhost:3000/admin/ad_dashboard.php';

$dateStart = $_POST['checkInDate'] ?? null;
$dateEnd   = $_POST['checkOutDate'] ?? null;
$checkIn   = toSqlDate($dateStart);
$checkOut  = toSqlDate($dateEnd);

// ✅ ดึงรายชื่อผู้เข้าพักจากฟอร์ม (ที่ JS สร้างไว้ให้)
$guestNames   = $_POST['guest_name']   ?? [];
$guestGenders = $_POST['guest_gender'] ?? [];
$guestPhones  = $_POST['guest_phone']  ?? [];

// (optional, แนะนำให้มี) ตรวจซ้ำฝั่ง PHP ว่าจำนวนรายชื่อ = จำนวนคน
$totalFromCount = max(0, $womanCount) + max(0, $manCount);
if ($totalFromCount <= 0) {
    jsonError('กรุณาระบุจำนวนผู้เข้าพักอย่างน้อย 1 คน');
}

if (!is_array($guestNames)) {
    $guestNames = [];
}
$filledGuests = 0;
foreach ($guestNames as $gName) {
    if (trim((string)$gName) !== '') {
        $filledGuests++;
    }
}
if ($filledGuests !== $totalFromCount) {
    jsonError('จำนวนรายชื่อผู้เข้าพัก (' . $filledGuests . ') ไม่ตรงกับจำนวนที่ระบุ (' . $totalFromCount . ')');
}

try {
    // เพิ่มข้อมูลลง bookings
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

    $bookingId = $conn->insert_id;
    if (!$bookingId) {
        jsonError('ไม่สามารถสร้างเลขที่ใบจองได้');
    }

    $bookingCode = formatBookingCode($bookingId, $checkIn);

    // ✅ บันทึกรายชื่อผู้เข้าพักลง booking_guest_requests
    if (is_array($guestNames) && count($guestNames) > 0) {

        $sqlGuest = "
            INSERT INTO booking_guest_requests
                (booking_id, guest_name, gender, guest_phone)
            VALUES (?, ?, ?, ?)
        ";
        $gStmt = $conn->prepare($sqlGuest);

        if ($gStmt) {
            $totalGuests = count($guestNames);

            for ($i = 0; $i < $totalGuests; $i++) {
                $name = trim((string)($guestNames[$i] ?? ''));
                if ($name === '') {
                    continue; // ข้าม empty row
                }

                $gender = $guestGenders[$i] ?? null;
                $gender = ($gender === 'F' || $gender === 'M') ? $gender : null;

                $phoneGuest = trim((string)($guestPhones[$i] ?? ''));

                $gStmt->bind_param(
                    'isss',
                    $bookingId,
                    $name,
                    $gender,
                    $phoneGuest
                );
                $gStmt->execute();
            }

            $gStmt->close();
        }
    }

    // ส่งเมลแจ้ง admin (เหมือนเดิม)
    $mailResult = sendNewBooking(
        $bookingCode,
        $fullName,
        $department,
        $dateStart ?? '',
        $dateEnd ?? '',
        $womanCount,
        $manCount,
        $adminUrl
    );

    if ($mailResult['success']) {
        echo json_encode([
            'status'      => 'success',
            'bookingCode' => $bookingCode
        ]);
    } else {
        echo json_encode([
            'status'      => 'success',
            'bookingCode' => $bookingCode,
            'mailError'   => $mailResult['error']
        ]);
    }

} catch (Throwable $e) {
    jsonError('เกิดข้อผิดพลาดในระบบ: ' . $e->getMessage());
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
