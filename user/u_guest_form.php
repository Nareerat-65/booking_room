<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../db.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    die('ลิงก์ไม่ถูกต้อง');
}

// ดึงข้อมูลการจองจาก token
$stmt = $conn->prepare("
    SELECT id, full_name, check_in_date, check_out_date,
           woman_count, man_count
    FROM bookings
    WHERE confirm_token = ?
      AND status = 'approved'
      AND (confirm_token_expires IS NULL OR confirm_token_expires >= NOW())
");
$stmt->bind_param('s', $token);
$stmt->execute();
$stmt->bind_result($bookingId, $bookerName, $checkIn, $checkOut, $totalW, $totalM);

if (!$stmt->fetch()) {
    $stmt->close();
    die('ลิงก์หมดอายุหรือไม่พบข้อมูลการจอง');
}
$stmt->close();


// ดึงรายการห้องที่จัดให้ booking นี้จาก room_allocations
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
$allocs = []; // [allocation_id] => ข้อมูลแถว

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


// ดึงรายชื่อผู้เข้าพักเดิม (ถ้ามี) จาก room_guests
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


// ถ้ามีการ submit ฟอร์ม (POST) → บันทึกรายชื่อ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = $_POST['guests'] ?? [];
    $phones = $_POST['guest_phones'] ?? [];  // ⭐ รับเบอร์มาด้วย

    foreach ($allocs as $aid => $a) {
        $aid = (int)$aid;

        $maxGuests = (int)$a['woman_count'] + (int)$a['man_count'];
        $gender = ((int)$a['woman_count'] > 0 && (int)$a['man_count'] === 0) ? 'F' : 'M';

        $names  = $posted[$aid] ?? [];
        $phonesPerAlloc = $phones[$aid] ?? [];   // ⭐ เบอร์ของห้องนี้

        if (!is_array($names)) $names = [];
        if (!is_array($phonesPerAlloc)) $phonesPerAlloc = [];

        // ทำความสะอาดชื่อ
        $cleanNames = [];
        $cleanPhones = [];

        foreach ($names as $idx => $n) {
            $n = trim((string)$n);
            if ($n !== '') {
                $cleanNames[]  = $n;
                $cleanPhones[] = trim($phonesPerAlloc[$idx] ?? ''); // ⭐ แมปเบอร์ตาม index
            }
        }

        // จำกัดจำนวนตาม maxGuests
        if (count($cleanNames) > $maxGuests) {
            $cleanNames  = array_slice($cleanNames, 0, $maxGuests);
            $cleanPhones = array_slice($cleanPhones, 0, $maxGuests);
        }

        // ลบข้อมูลเดิม
        $del = $conn->prepare("
            DELETE FROM room_guests
            WHERE booking_id = ? AND allocation_id = ?
        ");
        $del->bind_param('ii', $bookingId, $aid);
        $del->execute();
        $del->close();

        // insert ใหม่
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


?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>กรอกรายชื่อผู้เข้าพัก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Kanit&subset=thai,latin" rel="stylesheet" type="text/css" />
    <style>
        body {
            background: #fbf6f4ff;
            font-family: 'Kanit', sans-serif;
        }

        .navbar {
            font-size: 0.95rem;
            backdrop-filter: blur(12px);
            background-color: #F57B39;
        }

        .navbar-brand {
            font-size: 1.9rem;
        }

        .btn {
            background-color: #F57B39;
            border: 0;
        }
    </style>

</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold py-2 d-flex align-items-center" href="#">
                <img src="../img/Medicine_Naresuan.png" alt="Logo" width="80" height="80" class="me-3">
                <span style="line-height:1; font-size:1.8rem;">
                    ระบบจองห้องพัก
                </span>
            </a>
        </div>
    </nav>
    <div class="container py-4">
        <h2 class="mb-3">กรอกรายชื่อผู้เข้าพัก</h2>

        <div class="card mb-4">
            <div class="card-body">
                <p class="mb-1"><b>ผู้จอง:</b> <?= htmlspecialchars($bookerName, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="mb-1"><b>ช่วงเข้าพัก:</b>
                    <?= htmlspecialchars($checkIn, ENT_QUOTES, 'UTF-8') ?>
                    ถึง
                    <?= htmlspecialchars($checkOut, ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p class="mb-0"><b>จำนวนทั้งหมด:</b>
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
                                    <label class="form-label">
                                        ชื่อคนที่ <?= $i + 1 ?>:
                                    </label>
                                    <input
                                        type="text"
                                        name="guests[<?= $aid ?>][]"
                                        class="form-control"
                                        value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
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