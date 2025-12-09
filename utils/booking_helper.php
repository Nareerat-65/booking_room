<?php
// utils/booking_helper.php

/**
 * สร้างเลขรายการจอง เช่น RM-2025-00023
 *
 * @param int         $id           id จากตาราง bookings
 * @param string|null $checkInDate  วันที่เข้าพัก (Y-m-d) ถ้าไม่ส่งมา จะใช้ปีปัจจุบัน
 */
function formatBookingCode(int $id, ?string $checkInDate = null): string
{
    // เลือกปีจากวันที่เข้าพัก ถ้ามีให้มา ไม่งั้นใช้ปีปัจจุบัน
    if ($checkInDate) {
        $ts   = strtotime($checkInDate);
        $year = $ts ? date('Y', $ts) : date('Y');
    } else {
        $year = date('Y');
    }

    // เติมเลข 0 ข้างหน้าให้ครบ 5 หลัก เช่น 23 -> 00023
    $running = str_pad((string)$id, 5, '0', STR_PAD_LEFT);

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


