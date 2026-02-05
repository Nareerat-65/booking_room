<?php
// utils/booking_helper.php

/**
 * สร้างเลขรายการจอง เช่น RM-2025-00023
 *
 * @param int         $id           id จากตาราง bookings
 * @param string|null $checkInDate  วันที่เข้าพัก (Y-m-d) ถ้าไม่ส่งมา จะใช้ปีปัจจุบัน
 */
function formatBookingCode(int $id): string
{
    // เติมเลข 0 ข้างหน้าให้ครบ 5 หลัก เช่น 23 -> 00023
    $running = str_pad((string)$id, 3, '0', STR_PAD_LEFT);

    return "RM-{$running}";
}

function formatDate(string $dateStr): string
{
    $date = new DateTime($dateStr);
    return $date->format('d/m/Y');
}

function toSqlDate($d)
{
    if (!$d) return null;

    // รับได้ทั้ง Y-m-d และ d-m-Y
    $dt = DateTime::createFromFormat('Y-m-d', $d)
        ?: DateTime::createFromFormat('d-m-Y', $d);

    return $dt ? $dt->format('Y-m-d') : null;
}

function formatPosition(array $row): string
{
    $pos = $row['position'] ?? '';
    switch ($pos) {
        case 'student':
            $year = isset($row['student_year']) && $row['student_year'] !== ''
                ? $row['student_year'] : '–';
            return "นักศึกษา/นิสิตแพทย์ชั้นปีที่ {$year}";
        case 'intern':
            return 'แพทย์ใช้ทุน';
        case 'resident':
            return 'แพทย์ประจำบ้าน';
        case 'staff':
            return 'เจ้าหน้าที่';
        case 'other':
            $other = trim($row['position_other'] ?? '');
            return $other !== '' ? $other : 'อื่น ๆ';
        default:
            return '–';
    }
}

function formatPurpose(array $row): string
{
    if (($row['purpose'] ?? '') === 'study') {
        $course = trim($row['study_course'] ?? '');
        return $course !== ''
            ? "ศึกษารายวิชา {$course}"
            : "ศึกษารายวิชา (ไม่ระบุชื่อวิชา)";
    } else {
        return "Elective";
    }
    return $row['purpose'] ? $row['purpose'] : '-';
}

function thaiMonth($m)
{
    $arr = [
        1 => "มกราคม",
        2 => "กุมภาพันธ์",
        3 => "มีนาคม",
        4 => "เมษายน",
        5 => "พฤษภาคม",
        6 => "มิถุนายน",
        7 => "กรกฎาคม",
        8 => "สิงหาคม",
        9 => "กันยายน",
        10 => "ตุลาคม",
        11 => "พฤศจิกายน",
        12 => "ธันวาคม"
    ];
    return $arr[(int)$m] ?? '';
}

function buildRoomSummaryHtml(mysqli $conn, int $bookingId): string
{
    $sql = "
        SELECT 
            r.room_name,
            g.guest_name
        FROM room_allocations a
        JOIN rooms r 
            ON r.id = a.room_id
        LEFT JOIN room_guests g
            ON g.booking_id = a.booking_id
           AND g.allocation_id = a.id
        WHERE a.booking_id = ?
        ORDER BY r.room_name, g.id
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return '';

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();

    $byRoom = [];

    while ($row = $res->fetch_assoc()) {
        $roomName  = trim((string)($row['room_name'] ?? ''));
        $guestName = trim((string)($row['guest_name'] ?? ''));

        if ($roomName === '' || $guestName === '') continue;

        if (!isset($byRoom[$roomName])) {
            $byRoom[$roomName] = [];
        }
        $byRoom[$roomName][] = $guestName;
    }

    $stmt->close();

    if (empty($byRoom)) {
        return ''; // ไม่มีข้อมูล ไม่ต้องแสดงอะไร
    }

    // สร้าง HTML
    $html  = '<div style="background:#fafafa; border-radius:8px; padding:12px 14px;';
    $html .= 'border-left:4px solid #4e9bff; margin:10px 0 18px;">';
    $html .= '<p style="margin:0 0 8px 0;"><b>รายละเอียดการจัดห้องพัก:</b></p>';
    $html .= '<ul style="margin:0; padding-left:18px;">';

    foreach ($byRoom as $roomName => $guests) {
        $html .= '<li><b>ห้อง ' 
              . htmlspecialchars($roomName, ENT_QUOTES, "UTF-8")
              . ':</b> '
              . htmlspecialchars(implode(', ', $guests), ENT_QUOTES, "UTF-8")
              . '</li>';
    }

    $html .= '</ul></div>';

    return $html;
}

