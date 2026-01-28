<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/config.php';
require_once CONFIG_PATH . '/db.php';
require_once UTILS_PATH . '/booking_helper.php';

// ✅ กันข้อมูลรั่ว: ต้องเป็นแอดมินเท่านั้น
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

// ✅ รับช่วงวันที่จาก FullCalendar/Board เพื่อไม่ดึงทั้งหมด
$rangeStart = $_GET['start'] ?? null; // format: YYYY-MM-DD
$rangeEnd   = $_GET['end'] ?? null;   // format: YYYY-MM-DD

// fallback: ถ้าไม่ส่งมา ให้ดึงช่วง 60 วันจากวันนี้
if (!$rangeStart || !$rangeEnd) {
    $rangeStart = date('Y-m-d', strtotime('-30 days'));
    $rangeEnd   = date('Y-m-d', strtotime('+30 days'));
}

// หมายเหตุ: end ใน FullCalendar เป็น exclusive อยู่แล้ว
// เราจะเลือก allocation ที่ "ทับซ้อนช่วง" (start < rangeEnd AND end >= rangeStart)
$sql = "
    SELECT 
        a.id,
        a.booking_id,
        a.room_id,
        a.start_date,
        a.end_date,
        a.woman_count,
        a.man_count,
        r.room_name,
        b.full_name,
        GROUP_CONCAT(
            CONCAT(
                g.guest_name,
                CASE 
                    WHEN g.gender = 'F' THEN ' (หญิง)'
                    WHEN g.gender = 'M' THEN ' (ชาย)'
                    ELSE ''
                END,
                CASE 
                    WHEN g.guest_phone IS NOT NULL AND g.guest_phone <> '' 
                        THEN CONCAT(' - เบอร์โทร: ', g.guest_phone)
                    ELSE ''
                END
            )
            ORDER BY g.id
            SEPARATOR '\n'
        ) AS guest_list
    FROM room_allocations a
    JOIN rooms r     ON a.room_id    = r.id
    JOIN bookings b  ON a.booking_id = b.id
    LEFT JOIN room_guests g 
           ON g.allocation_id = a.id
          AND g.booking_id    = a.booking_id
    WHERE b.status = 'approved'
      AND a.start_date < ?
      AND a.end_date >= ?
    GROUP BY 
        a.id, a.booking_id, a.room_id, a.start_date, a.end_date,
        a.woman_count, a.man_count, r.room_name, b.full_name
    ORDER BY a.start_date, r.id
";

$roomColors = [
    1 => '#64b5f6',
    2 => '#64b5f6',
    3 => '#64b5f6',
    4 => '#64b5f6',
    5 => '#64b5f6',
    6 => '#64b5f6',
];

// ใช้ prepared เพื่อปลอดภัยและรองรับพารามิเตอร์
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $rangeEnd, $rangeStart);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $bookingCode = formatBookingCode($row['booking_id']);
    $roomName    = $row['room_name'];
    $roomId      = (int)$row['room_id'];
    $checkIn     = $row['start_date'];
    $checkOut    = $row['end_date'];

    $cleanStart = date('Y-m-d', strtotime($checkOut . ' +1 day'));
    $cleanEnd   = date('Y-m-d', strtotime($checkOut . ' +3 day'));

    $w = (int)$row['woman_count'];
    $m = (int)$row['man_count'];

    // ✅ ทำ title ให้สั้นลง (อ่านง่ายขึ้น)
    $pieces = [$bookingCode];
    if ($w > 0) $pieces[] = "หญิง {$w}";
    if ($m > 0) $pieces[] = "ชาย {$m}";
    $titleMain = implode(' ', $pieces);

    $guestList = $row['guest_list'] ?: 'ยังไม่มีรายชื่อผู้เข้าพัก';

    $color = $roomColors[$roomId] ?? '#0d6efd';

    // stay
    $events[] = [
        'id'     => (string)$row['id'],
        'title'  => $titleMain,
        'start'  => $checkIn,
        'end'    => date('Y-m-d', strtotime($checkOut . ' +1 day')),
        'allDay' => true,
        'color'  => $color,
        'extendedProps' => [
            'room_id'      => $roomId,
            'room'         => $roomName,
            'booking_id'   => (int)$row['booking_id'],
            'booking_code' => $bookingCode,
            'booker'       => $row['full_name'],
            'start_real'   => formatDate($checkIn),
            'end_real'     => formatDate($checkOut),
            'guests'       => $guestList,
            'type'         => 'stay',
        ],
    ];

    // cleaning (3 days)
    $events[] = [
        'id'     => 'clean-' . $row['id'],
        'title'  => "ทำความสะอาด",
        'start'  => $cleanStart,
        'end'    => date('Y-m-d', strtotime($cleanEnd . ' +1 day')),
        'allDay' => true,
        'color'  => '#9e9e9e',
        'textColor' => '#fff',
        'extendedProps' => [
            'room_id'    => $roomId,
            'room'       => $roomName,
            'booking_id' => (int)$row['booking_id'],
            'booker'     => $row['full_name'],
            'start_real' => $cleanStart,
            'end_real'   => $cleanEnd,
            'type'       => 'cleaning',
        ],
    ];
}

$stmt->close();
$conn->close();

echo json_encode($events, JSON_UNESCAPED_UNICODE);
