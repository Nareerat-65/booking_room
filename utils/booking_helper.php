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
