<?php
// guest_fill.php
// หน้าให้ผู้จองกรอกรายชื่อผู้เข้าพักจากลิงก์ในอีเมล

header('Content-Type: text/html; charset=utf-8');
require_once '../db.php'; // ปรับ path ให้ตรงกับไฟล์เชื่อม DB ของโปรเจกต์

$token = $_GET['token'] ?? '';
if ($token === '') {
    die('ลิงก์ไม่ถูกต้อง');
}

// -------------------------------------------
// 1) ดึงข้อมูลการจองจาก token
// -------------------------------------------
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

// -------------------------------------------
// 2) ดึงรายการห้องที่จัดให้ booking นี้จาก room_allocations
// -------------------------------------------
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

// -------------------------------------------
// 3) ดึงรายชื่อผู้เข้าพักเดิม (ถ้ามี) จาก room_guests
// -------------------------------------------
$sqlGuests = "
    SELECT allocation_id, guest_name
    FROM room_guests
    WHERE booking_id = ?
    ORDER BY id
";
$guests = []; // [allocation_id] => [ 'ชื่อ1', 'ชื่อ2', ... ]

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

// -------------------------------------------
// 4) ถ้ามีการ submit ฟอร์ม (POST) → บันทึกรายชื่อ
// -------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = $_POST['guests'] ?? []; // guests[allocation_id][] = ชื่อ

    // วนทุก allocation ที่จัดไว้
    foreach ($allocs as $aid => $a) {
        $aid = (int)$aid;

        $maxGuests = (int)$a['woman_count'] + (int)$a['man_count']; // ห้องนี้ให้ได้กี่คน
        $gender    = ((int)$a['woman_count'] > 0 && (int)$a['man_count'] === 0) ? 'F' : 'M';

        $names = $posted[$aid] ?? [];
        if (!is_array($names)) {
            $names = [];
        }

        // ล้างชื่อ: trim + ตัดช่องว่างที่ว่างเปล่าออก
        $cleanNames = [];
        foreach ($names as $n) {
            $n = trim((string)$n);
            if ($n !== '') {
                $cleanNames[] = $n;
            }
        }

        // ถ้าชื่อเยอะกว่าจำนวนคนที่ห้องนี้รองรับ → ตัดทิ้งส่วนเกิน
        if (count($cleanNames) > $maxGuests) {
            $cleanNames = array_slice($cleanNames, 0, $maxGuests);
        }

        // ลบข้อมูลเดิมของห้องนี้ก่อน แล้วค่อย insert ใหม่
        $del = $conn->prepare("
            DELETE FROM room_guests
            WHERE booking_id = ? AND allocation_id = ?
        ");
        $del->bind_param('ii', $bookingId, $aid);
        $del->execute();
        $del->close();

        if (!empty($cleanNames)) {
            $ins = $conn->prepare("
                INSERT INTO room_guests (booking_id, allocation_id, guest_name, gender)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($cleanNames as $gname) {
                $ins->bind_param('iiss', $bookingId, $aid, $gname, $gender);
                $ins->execute();
            }
            $ins->close();
        }
    }

    $saveMessage = 'บันทึกรายชื่อเรียบร้อยแล้ว ขอบคุณค่ะ 🙏';

    // โหลดข้อมูลใหม่มาแสดง (จะได้เห็นชื่อที่เพิ่งกรอก)
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
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>กรอกรายชื่อผู้เข้าพัก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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
                        <div class="mb-2">
                            <label class="form-label">
                                ชื่อคนที่ <?= $i + 1 ?>:
                            </label>
                            <input
                                type="text"
                                name="guests[<?= $aid ?>][]"
                                class="form-control"
                                value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>
                    <?php endfor; ?>

                    <p class="text-muted small mb-0">
                        * ถ้าไม่ครบทุกช่อง ให้กรอกเฉพาะจำนวนคนที่เข้าพักจริง ที่เหลือปล่อยว่างได้
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
