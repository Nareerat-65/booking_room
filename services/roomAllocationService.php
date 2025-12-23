<?php
// services/roomAllocationService.php

require_once __DIR__ . '/../repositories/roomAllocationRepo.php';
require_once __DIR__ . '/../repositories/guestRequestRepo.php';

/**
 * จัดสรรห้องแบบ strict:
 * - อ่านจำนวนหญิง/ชาย + วันที่จาก bookings
 * - หา rooms ว่าง (มี buffer end_date + 3 วัน)
 * - insert room_allocations แยกหญิงก่อน แล้วชาย
 *
 * หมายเหตุ: ควรถูกเรียกภายใน transaction (เช่น approveBooking)
 */
function allocateRoomsStrict(mysqli $conn, int $bookingId): bool
{
    $bk = alloc_getBookingCountsAndDates($conn, $bookingId);
    if (!$bk) return false;

    $remainW  = (int)($bk['woman_count'] ?? 0);
    $remainM  = (int)($bk['man_count'] ?? 0);
    $startDate = (string)($bk['check_in_date'] ?? '');
    $endDate   = (string)($bk['check_out_date'] ?? '');

    if ($startDate === '' || $endDate === '') return false;

    $rooms = alloc_findAvailableRoomsWithBuffer($conn, $startDate, $endDate);
    if (empty($rooms)) return false;

    $roomIndex = 0;

    // ผู้หญิง
    while ($remainW > 0 && $roomIndex < count($rooms)) {
        $roomId = (int)$rooms[$roomIndex]['id'];
        $cap    = (int)$rooms[$roomIndex]['capacity'];
        $num    = min($cap, $remainW);

        $ok = alloc_insertAllocation($conn, $bookingId, $roomId, $startDate, $endDate, $num, 0);
        if (!$ok) return false;

        $remainW -= $num;
        $roomIndex++;
    }

    // ผู้ชาย
    while ($remainM > 0 && $roomIndex < count($rooms)) {
        $roomId = (int)$rooms[$roomIndex]['id'];
        $cap    = (int)$rooms[$roomIndex]['capacity'];
        $num    = min($cap, $remainM);

        $ok = alloc_insertAllocation($conn, $bookingId, $roomId, $startDate, $endDate, 0, $num);
        if (!$ok) return false;

        $remainM -= $num;
        $roomIndex++;
    }

    return ($remainW === 0 && $remainM === 0);
}

/**
 * เติมรายชื่อผู้เข้าพักจาก booking_guest_requests ลง room_guests ตาม allocations
 * - ดึง allocations ของ booking
 * - ดึง guest requests
 * - ลบ room_guests เดิมของ allocation แล้ว insert ใหม่ตามจำนวนคนในห้องนั้น
 *
 * หมายเหตุ: ควรถูกเรียกภายใน transaction เดียวกับ allocateRoomsStrict()
 */
function autoFillRoomGuestsFromRequests(mysqli $conn, int $bookingId): bool
{
    $allocs = alloc_listAllocationsByBookingId($conn, $bookingId);
    if (empty($allocs)) {
        // ยังไม่จัดสรรห้อง → ไม่มีอะไรให้เติม ถือว่า ok
        return true;
    }

    $requests = guestRequest_listByBookingId($conn, $bookingId);
    if (empty($requests)) return true;

    $women = [];
    $men = [];
    $unknown = [];

    foreach ($requests as $r) {
        $g = strtoupper(trim((string)($r['gender'] ?? '')));
        if ($g === 'F') $women[] = $r;
        elseif ($g === 'M') $men[] = $r;
        else $unknown[] = $r;
    }

    $popGuest = function (string $wantGender) use (&$women, &$men, &$unknown) {
        if ($wantGender === 'F') {
            if (!empty($women))   return array_shift($women);
            if (!empty($unknown)) return array_shift($unknown);
        } else { // 'M'
            if (!empty($men))     return array_shift($men);
            if (!empty($unknown)) return array_shift($unknown);
        }
        return null;
    };

    foreach ($allocs as $a) {
        $allocationId = (int)($a['allocation_id'] ?? 0);
        $womanCount   = (int)($a['woman_count'] ?? 0);
        $manCount     = (int)($a['man_count'] ?? 0);

        $maxGuests = $womanCount + $manCount;
        if ($allocationId <= 0 || $maxGuests <= 0) continue;

        // เพศหลักของห้อง (คง logic เดิมของคุณ)
        $gender = ($womanCount > 0 && $manCount === 0) ? 'F' : 'M';

        // เคลียร์ของเดิม
        if (!guest_deleteByBookingAndAllocation($conn, $bookingId, $allocationId)) {
            return false;
        }

        $inserted = 0;
        while ($inserted < $maxGuests) {
            $guest = $popGuest($gender);
            if ($guest === null) break;

            $name = trim((string)($guest['guest_name'] ?? ''));
            if ($name === '') continue;

            $phone = trim((string)($guest['guest_phone'] ?? ''));

            $ok = guest_insertByAllocation($conn, $bookingId, $allocationId, $name, $phone, $gender);
            if (!$ok) return false;

            $inserted++;
        }
    }

    return true;
}
