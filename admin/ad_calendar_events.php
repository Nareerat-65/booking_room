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

// สีประจำห้อง
$roomColors = [
    1 => '#e57373',   // ห้อง 1
    2 => '#64b5f6',   // ห้อง 2
    3 => '#81c784',   // ห้อง 3
    4 => '#ffb74d',   // ห้อง 4
    5 => '#ba68c8',   // ห้อง 5
    6 => '#ff65c4',   // ห้อง 6
];

$result = $conn->query($sql);
$events = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $roomName   = $row['room_name'];
        $roomId     = (int)$row['room_id'];
        $startDate  = $row['start_date'];   // วันที่เข้าพัก
        $endDateRaw = $row['end_date'];     // วันที่ออกจริง (คืนสุดท้าย)

        // ช่วงทำความสะอาด: 3 วันถัดจากวันออก
        $cleanStart = date('Y-m-d', strtotime($endDateRaw . ' +1 day'));
        $cleanEnd   = date('Y-m-d', strtotime($endDateRaw . ' +3 day'));

        $w = (int)$row['woman_count'];
        $m = (int)$row['man_count'];

        // title หลักของช่วงเข้าพัก: แสดงห้อง + จำนวนหญิง/ชาย
        $pieces = [$roomName];
        if ($w > 0) $pieces[] = "หญิง {$w}";
        if ($m > 0) $pieces[] = "ชาย {$m}";
        $titleMain = implode(' • ', $pieces);

        // tooltip เวลา hover
        $tooltip = "ผู้จอง: {$row['full_name']}\n"
            . "{$roomName}\n"
            . "วันเข้าพัก: {$startDate}\n"
            . "วันออก: {$endDateRaw}\n";


        $color = $roomColors[$roomId] ?? '#0d6efd';

        // รายชื่อเต็มไว้โชว์ใน modal
        $guestList = $row['guest_list'] ?? '';
        if ($guestList === null || $guestList === '') {
            $guestList = 'ยังไม่มีรายชื่อผู้เข้าพัก';
        }

        // 🔹 Event 1: ช่วงเข้าพักจริง
        $events[] = [
            'id'      => $row['id'],   // id ของ allocation
            'title'   => $titleMain,
            'start'   => $startDate,
            'end'     => date('Y-m-d', strtotime($endDateRaw . ' +1 day')), // end exclusive
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

        // 🔹 Event 2: ช่วงทำความสะอาด (3 วันถัดไป)
        $events[] = [
            'id'      => 'clean-' . $row['id'],
            'title'   => "{$roomName} (ทำความสะอาด)",
            'start'   => $cleanStart,
            'end'     => date('Y-m-d', strtotime($cleanEnd . ' +1 day')), // end exclusive
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
