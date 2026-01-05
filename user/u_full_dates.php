<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 1) . '/config.php';
require_once CONFIG_PATH . '/db.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode([]);
    exit;
}

$today   = new DateTime('today');
$startDt = (clone $today)->modify('+14 days');
$endDt   = (clone $startDt)->modify('+6 months');

$startDate = $startDt->format('Y-m-d');
$endDate   = $endDt->format('Y-m-d');

// 1) จำนวนห้องทั้งหมด
$totalRooms = 0;
$sqlRooms = "SELECT COUNT(*) AS total_rooms FROM rooms";
if ($res = $conn->query($sqlRooms)) {
    $row = $res->fetch_assoc();
    $totalRooms = (int)($row['total_rooms'] ?? 0);
    $res->free();
}

if ($totalRooms <= 0) {
    echo json_encode([]);
    exit;
}

$sqlAlloc = "
    SELECT a.room_id, a.start_date, a.end_date
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

// 3) เก็บ set ของ room_id ต่อวัน
$usedRoomsByDate = [];

$inclusiveEnd = true; // ถ้า end_date เป็น "วันออก" ให้เปลี่ยนเป็น false
   
while ($row = $res->fetch_assoc()) {
    $roomId = (int)($row['room_id'] ?? 0);
    $allocStart = $row['start_date'] ?? null;
    $allocEnd   = $row['end_date'] ?? null;

    if ($roomId <= 0 || !$allocStart || !$allocEnd) continue;

    $dStart = new DateTime($allocStart);
    $dEnd   = new DateTime($allocEnd);

    // กันข้อมูลสลับวัน
    if ($dStart > $dEnd) continue;

    // ถ้า end_date คือวันออก (ไม่ค้าง) ให้ไม่นับวันนั้น
    if (!$inclusiveEnd) {
        $dEnd->modify('-1 day');
        if ($dStart > $dEnd) continue; // เช่น พัก 1 คืนแบบ start=end_date
    }

    // จำกัดให้อยู่ในช่วงที่สนใจ
    if ($dStart < $startDt) $dStart = clone $startDt;
    if ($dEnd   > $endDt)   $dEnd   = clone $endDt;
    if ($dStart > $dEnd) continue;

    while ($dStart <= $dEnd) {
        $key = $dStart->format('Y-m-d');
        if (!isset($usedRoomsByDate[$key])) $usedRoomsByDate[$key] = [];
        $usedRoomsByDate[$key][$roomId] = true;

        $dStart->modify('+1 day');
    }
}

$stmt->close();

// 4) วันเต็ม = ห้องถูกใช้ครบทุกห้อง
$fullDates = [];
foreach ($usedRoomsByDate as $date => $roomSet) {
    if (count($roomSet) >= $totalRooms) $fullDates[] = $date;
}

echo json_encode(array_values($fullDates));
