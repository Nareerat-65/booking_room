<?php
// user/api_get_full_dates.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

// ตรวจสอบว่าเชื่อมต่อฐานข้อมูลได้หรือไม่
if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode([]);
    exit;
}

// -------------------- 1) กำหนดช่วงวันที่ที่จะตรวจสอบ --------------------
$today   = new DateTime('today');
// ถ้าอยากให้ตรงกับหน้า booking (minCheckIn = วันนี้ + 14 วัน) ก็ทำแบบนี้:
$startDt = (clone $today)->modify('+14 days');
// ตรวจไปล่วงหน้า 6 เดือน (ปรับได้)
$endDt   = (clone $startDt)->modify('+6 months');

$startDate = $startDt->format('Y-m-d');
$endDate   = $endDt->format('Y-m-d');

// -------------------- 2) หา capacity รวมทั้งหมดของทุกห้อง --------------------
$totalCapacity = 0;

$sqlCap = "SELECT SUM(capacity) AS total_cap FROM rooms";
if ($resCap = $conn->query($sqlCap)) {
    if ($rowCap = $resCap->fetch_assoc()) {
        $totalCapacity = (int)($rowCap['total_cap'] ?? 0);
    }
    $resCap->free();
}

// ถ้าไม่มีห้องเลย ก็ถือว่ายังไม่มีวันที่จะ disable (หรือจะให้เต็มทุกวันก็แล้วแต่ design)
if ($totalCapacity <= 0) {
    echo json_encode([]);
    exit;
}

// -------------------- 3) ดึง allocation ที่อนุมัติแล้วและทับซ้อนช่วงวันที่ --------------------
$sqlAlloc = "
    SELECT 
        a.start_date,
        a.end_date,
        a.woman_count,
        a.man_count
    FROM room_allocations a
    INNER JOIN bookings b ON b.id = a.booking_id
    WHERE b.status = 'approved'
      AND a.end_date >= ?
      AND a.start_date <= ?
";

$stmt = $conn->prepare($sqlAlloc);
if (!$stmt) {
    echo json_encode([]);
    exit;
}

$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$res = $stmt->get_result();

// array เก็บจำนวนคนต่อวัน เช่น ['2025-01-10' => 12, ...]
$occupancy = [];

// loop allocation ทุกแถว แล้วแตกเป็นรายวัน
while ($row = $res->fetch_assoc()) {
    $allocStart = $row['start_date'] ?? null;
    $allocEnd   = $row['end_date'] ?? null;

    if (!$allocStart || !$allocEnd) {
        continue;
    }

    $count = (int)($row['woman_count'] ?? 0) + (int)($row['man_count'] ?? 0);
    if ($count <= 0) {
        continue;
    }

    $dStart = new DateTime($allocStart);
    $dEnd   = new DateTime($allocEnd);

    // บังคับให้ไม่ออกนอกช่วงที่เราสนใจ
    if ($dStart < $startDt) $dStart = clone $startDt;
    if ($dEnd   > $endDt)   $dEnd   = clone $endDt;

    // เดินวันจาก start → end (ถือว่าแต่ละวันในช่วงนี้มีคนพัก)
    while ($dStart <= $dEnd) {
        $key = $dStart->format('Y-m-d');
        if (!isset($occupancy[$key])) {
            $occupancy[$key] = 0;
        }
        $occupancy[$key] += $count;

        $dStart->modify('+1 day');
    }
}

$stmt->close();

// -------------------- 4) หา “วันที่เต็มแล้ว” --------------------
$fullDates = [];

foreach ($occupancy as $date => $numPeople) {
    if ($numPeople >= $totalCapacity) {
        $fullDates[] = $date;
    }
}

// ส่งออกเป็น JSON
echo json_encode($fullDates);
