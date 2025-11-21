<?php
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
    GROUP BY 
        a.id,
        a.booking_id,
        a.room_id,
        a.start_date,
        a.end_date,
        a.woman_count,
        a.man_count,
        r.room_name,
        b.full_name
    ORDER BY a.start_date, r.id
";


$roomColors = [
    1 => '#e57373',   
    2 => '#64b5f6',   
    3 => '#81c784',   
    4 => '#ffb74d',   
    5 => '#ba68c8',   
    6 => '#ff65c4',   
];

$result = $conn->query($sql);
$events = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $roomName   = $row['room_name'];
        $roomId     = (int)$row['room_id'];
        $startDate  = $row['start_date'];   
        $endDateRaw = $row['end_date'];     

        $cleanStart = date('Y-m-d', strtotime($endDateRaw . ' +1 day'));
        $cleanEnd   = date('Y-m-d', strtotime($endDateRaw . ' +3 day'));

        $w = (int)$row['woman_count'];
        $m = (int)$row['man_count'];

        $pieces = [$roomName];
        if ($w > 0) $pieces[] = "หญิง {$w}";
        if ($m > 0) $pieces[] = "ชาย {$m}";
        $titleMain = implode(' • ', $pieces);

        $tooltip = "ผู้จอง: {$row['full_name']}\n"
            . "{$roomName}\n"
            . "วันเข้าพัก: {$startDate}\n"
            . "วันออก: {$endDateRaw}\n";


        $color = $roomColors[$roomId] ?? '#0d6efd';

        $guestList = $row['guest_list'] ?? '';
        if ($guestList === null || $guestList === '') {
            $guestList = 'ยังไม่มีรายชื่อผู้เข้าพัก';
        }

        $events[] = [
            'id'      => $row['id'],   
            'title'   => $titleMain,
            'start'   => $startDate,
            'end'     => date('Y-m-d', strtotime($endDateRaw . ' +1 day')), 
            'allDay'  => true,
            'color'   => $color,
            'extendedProps' => [
                'tooltip'    => $tooltip,
                'room'       => $roomName,
                'booking_id' => $row['booking_id'],
                'booker'     => $row['full_name'],
                'start_real' => $startDate,
                'end_real'   => $endDateRaw,
                'guests'     => $guestList,
                'type'       => 'stay',
            ],
        ];
        $events[] = [
            'id'      => 'clean-' . $row['id'],
            'title'   => "{$roomName} (ทำความสะอาด)",
            'start'   => $cleanStart,
            'end'     => date('Y-m-d', strtotime($cleanEnd . ' +1 day')), 
            'allDay'  => true,
            'color'   => '#999999',
            'textColor' => '#ffffff',
            'extendedProps' => [
                'room'       => $roomName,
                'booking_id' => $row['booking_id'],
                'booker'     => $row['full_name'],
                'start_real' => $cleanStart,
                'end_real'   => $cleanEnd,
                'type'       => 'cleaning',
            ],
        ];
    }
    $result->free();
}

echo json_encode($events);
$conn->close();
