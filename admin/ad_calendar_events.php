<?php
// ad_calendar_events.php
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

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
    GROUP_CONCAT(g.guest_name SEPARATOR ', ') AS guests
FROM room_allocations a
JOIN rooms r     ON a.room_id   = r.id
JOIN bookings b  ON a.booking_id = b.id
LEFT JOIN room_guests g ON g.allocation_id = a.id
WHERE b.status = 'approved'
GROUP BY 
    a.id, a.booking_id, a.room_id, a.start_date, a.end_date,
    a.woman_count, a.man_count, r.room_name, b.full_name
ORDER BY a.start_date, r.id
";


$result = $conn->query($sql);
$events = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $roomName   = $row['room_name'];
        $startDate  = $row['start_date'];
        $endDateRaw = $row['end_date'];

        // FullCalendar ใช้ end แบบ "exclusive"
        // ถ้าอยากให้แสดง: [วันเข้าพัก..วันออกจริง + 3 วันทำความสะอาด]
        // => end_exclusive = end_date + 4 วัน
        $endDateExclusive = date('Y-m-d', strtotime($endDateRaw . ' +4 day'));

        $w = (int)$row['woman_count'];
        $m = (int)$row['man_count'];

        $pieces = [$roomName];
        if ($w > 0) $pieces[] = "หญิง {$w}";
        if ($m > 0) $pieces[] = "ชาย {$m}";
        $title = implode(' • ', $pieces);

        // tooltip แสดงรายละเอียดเวลา hover
        $guestList = $row['guests'] ?: 'ยังไม่กรอกรายชื่อ';
        $tooltip = "รายชื่อผู้เข้าพัก: {$guestList}";


        // สี event แยกตามเพศ
        $roomId = (int)$row['room_id'];

        // map สีตามห้อง
        $roomColors = [
            1 => '#e57373', // ห้อง 1
            2 => '#64b5f6', // ห้อง 2
            3 => '#81c784', // ห้อง 3
            4 => '#ffb74d', // ห้อง 4
            5 => '#ba68c8', // ห้อง 5
            6 => '#4db6ac', // ห้อง 6
        ];

        // ถ้าไม่มีใน map ใช้สี default
        $color = $roomColors[$roomId] ?? '#0d6efd';

        $events[] = [
            'id'    => $row['id'],
            'title' => $title,
            'start' => $startDate,
            'end'   => $endDateExclusive, // exclusive
            'allDay' => true,
            'color' => $color,
            'extendedProps' => [
                'tooltip' => $tooltip,
                'room'    => $roomName,
                'booking_id' => $row['booking_id'],
            ],
        ];
    }
    $result->free();
}

echo json_encode($events);
$conn->close();
