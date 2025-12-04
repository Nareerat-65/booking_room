<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../db.php';
require_once '../utils/booking_helper.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    die('ลิงก์ไม่ถูกต้อง');
}

$stmt = $conn->prepare("
    SELECT id, full_name, department, check_in_date, check_out_date,
           woman_count, man_count
    FROM bookings
    WHERE confirm_token = ?
      AND status = 'approved'
      AND (confirm_token_expires IS NULL OR confirm_token_expires >= NOW())
");
$stmt->bind_param('s', $token);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    die('ลิงก์หมดอายุหรือไม่พบข้อมูลการจอง');
}

$bookingId = (int)$booking['id'];
$fullName  = $booking['full_name'] ?? '';
$department= $booking['department'] ?? ''; 
$checkIn   = $booking['check_in_date'] ?? '';
$checkOut  = $booking['check_out_date'] ?? '';
$totalW    = (int)($booking['woman_count'] ?? 0);
$totalM    = (int)($booking['man_count'] ?? 0);

$bookingCode = formatBookingCode($bookingId);

$sqlAlloc = "
    SELECT 
        a.id AS allocation_id,
        a.room_id,
        a.woman_count,
        a.man_count,
        r.room_name
    FROM room_allocations a
    JOIN rooms r ON a.room_id = r.id
    WHERE a.booking_id = ?
    ORDER BY r.id, a.id
";
$allocs = [];

$stmt = $conn->prepare($sqlAlloc);
$stmt->bind_param('i', $bookingId);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $allocs[(int)$row['allocation_id']] = $row;
}
$stmt->close();

if (empty($allocs)) {
    die('ยังไม่ได้จัดสรรห้องสำหรับการจองนี้ กรุณาติดต่อเจ้าหน้าที่');
}

$sqlGuests = "
    SELECT allocation_id, guest_name
    FROM room_guests
    WHERE booking_id = ?
    ORDER BY id
";
$guests = [];

$stmt = $conn->prepare($sqlGuests);
$stmt->bind_param('i', $bookingId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $aid = (int)$row['allocation_id'];
    if (!isset($guests[$aid])) {
        $guests[$aid] = [];
    }
    $guests[$aid][] = $row['guest_name'];
}
$stmt->close();

$saveMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = $_POST['guests'] ?? [];
    $phones = $_POST['guest_phones'] ?? [];

    foreach ($allocs as $aid => $a) {
        $aid = (int)$aid;

        $maxGuests = (int)$a['woman_count'] + (int)$a['man_count'];
        $gender = ((int)$a['woman_count'] > 0 && (int)$a['man_count'] === 0) ? 'F' : 'M';

        $names  = $posted[$aid] ?? [];
        $phonesPerAlloc = $phones[$aid] ?? [];

        if (!is_array($names)) $names = [];
        if (!is_array($phonesPerAlloc)) $phonesPerAlloc = [];

        $cleanNames = [];
        $cleanPhones = [];

        foreach ($names as $idx => $n) {
            $n = trim((string)$n);
            if ($n !== '') {
                $cleanNames[]  = $n;
                $cleanPhones[] = trim($phonesPerAlloc[$idx] ?? '');
            }
        }

        if (count($cleanNames) > $maxGuests) {
            $cleanNames  = array_slice($cleanNames, 0, $maxGuests);
            $cleanPhones = array_slice($cleanPhones, 0, $maxGuests);
        }

        $del = $conn->prepare("
            DELETE FROM room_guests
            WHERE booking_id = ? AND allocation_id = ?
        ");
        $del->bind_param('ii', $bookingId, $aid);
        $del->execute();
        $del->close();

        if (!empty($cleanNames)) {
            $ins = $conn->prepare("
                INSERT INTO room_guests (booking_id, allocation_id, guest_name, guest_phone, gender)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($cleanNames as $i => $gname) {
                $gphone = $cleanPhones[$i] ?? '';

                $ins->bind_param(
                    'iisss',
                    $bookingId,
                    $aid,
                    $gname,
                    $gphone,
                    $gender
                );
                $ins->execute();
            }
            $ins->close();
        }
    }
    $saveMessage = 'บันทึกรายชื่อผู้เข้าพักเรียบร้อยแล้ว';
}

$pageTitle = 'กรอกรายชื่อผู้เข้าพัก - ระบบจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="/assets/css/user/u_guest_form.css">';

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/user/head_user.php'; ?>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold py-2 d-flex align-items-center" href="#">
                <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" width="80" height="80" class="me-3">
                <span style="line-height:1; font-size:1.8rem;">
                    ระบบจองห้องพัก
                </span>
            </a>
        </div>
    </nav>
    <div class="container py-4">
        <h2 class=" text-center mb-4">กรอกรายชื่อผู้เข้าพัก</h2>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-1">
                    <b>เลขที่ใบจอง #<?= htmlspecialchars($bookingCode) ?></b>
                </h4>
                <p class="mb-1"><b>ผู้จอง :</b> <?= htmlspecialchars($fullName) ?></p>
                <p class="mb-1"><b>ชื่อหน่วยงานต้นสังกัด :</b> <?= htmlspecialchars($department ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <p class="mb-1"><b>ช่วงวันที่เข้าพัก :</b>
                    <?= htmlspecialchars($checkIn ?? '', ENT_QUOTES, 'UTF-8') ?>
                    ถึง
                    <?= htmlspecialchars($checkOut ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p class="mb-0"><b>จำนวนทั้งหมด :</b>
                    หญิง <?= (int)$totalW ?> คน,
                    ชาย <?= (int)$totalM ?> คน
                </p>
            </div>
        </div>

        <?php if ($saveMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($saveMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" class="mb-5">

            <?php foreach ($allocs as $aid => $a): ?>
                <?php
                $aid       = (int)$aid;
                $roomName  = $a['room_name'];
                $wCount    = (int)$a['woman_count'];
                $mCount    = (int)$a['man_count'];
                $maxGuests = $wCount + $mCount;
                $genderLbl = ($wCount > 0 && $mCount === 0) ? 'หญิง' : 'ชาย';
                $existing  = $guests[$aid] ?? [];
                ?>

                <div class="card mb-3">
                    <div class="card-header">
                        ห้อง: <?= htmlspecialchars($roomName, ENT_QUOTES, 'UTF-8') ?>
                        (<?= $genderLbl ?> สูงสุด <?= $maxGuests ?> คน)
                    </div>
                    <div class="card-body">

                        <?php for ($i = 0; $i < $maxGuests; $i++): ?>
                            <?php
                            $value = $existing[$i] ?? '';
                            ?>
                            <div class="row g-2 align-items-center mb-2">
                                <div class="mb-2">
                                    <label class="form-label required">
                                        ชื่อคนที่ <?= $i + 1 ?>:
                                    </label>
                                    <input
                                        type="text"
                                        name="guests[<?= $aid ?>][]"
                                        class="form-control"
                                        value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                        <?php if ($i === 0): ?>required<?php endif; ?>>

                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small">
                                        เบอร์โทรผู้เข้าพักคนที่ <?= $i + 1 ?>
                                    </label>
                                    <input
                                        type="tel"
                                        name="guest_phones[<?= $aid ?>][]"
                                        class="form-control"
                                        placeholder="เช่น 0812345678">
                                </div>
                            </div>
                        <?php endfor; ?>

                        <p class="text-muted small mb-0">
                            *ให้กรอกเฉพาะจำนวนคนที่เข้าพักจริงและเบอร์โทรอย่างน้อย 1 คนต่อห้อง ที่เหลือปล่อยว่างไว้
                        </p>
                    </div>
                </div>

            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary">
                บันทึกรายชื่อผู้เข้าพัก
            </button>
        </form>
    </div>
</body>

</html>