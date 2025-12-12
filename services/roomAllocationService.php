<?php
require_once __DIR__ . '/../db.php';

function allocateRoomsStrict(mysqli $conn, int $bookingId): bool
{
    $stmt = $conn->prepare("
        SELECT woman_count, man_count, check_in_date, check_out_date
        FROM bookings
        WHERE id = ?
    ");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $stmt->bind_result($womanCount, $manCount, $checkIn, $checkOut);
    if (!$stmt->fetch()) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    $startDate = $checkIn;
    $endDate   = $checkOut;
    $rooms     = [];

    $sqlRooms = "
        SELECT r.id, r.capacity
        FROM rooms r
        WHERE r.id NOT IN (
            SELECT DISTINCT ra.room_id
            FROM room_allocations ra
            WHERE NOT (
                DATE_ADD(ra.end_date, INTERVAL 3 DAY) < ?
                OR ra.start_date > ?
            )
        )
        ORDER BY r.id ASC
    ";

    $stmt = $conn->prepare($sqlRooms);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $rooms[] = $row;
    }
    $stmt->close();

    if (empty($rooms)) {
        return false;
    }

    $roomIndex = 0;
    $insert = $conn->prepare("
        INSERT INTO room_allocations
            (booking_id, room_id, start_date, end_date, woman_count, man_count)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $remainW = (int)$womanCount;
    $remainM = (int)$manCount;

    // ผู้หญิง
    while ($remainW > 0 && $roomIndex < count($rooms)) {
        $roomId = (int)$rooms[$roomIndex]['id'];
        $cap    = (int)$rooms[$roomIndex]['capacity'];
        $num    = min($cap, $remainW);

        $zero = 0;
        $insert->bind_param(
            'iissii',
            $bookingId,
            $roomId,
            $startDate,
            $endDate,
            $num,
            $zero
        );
        $insert->execute();

        $remainW   -= $num;
        $roomIndex += 1;
    }

    // ผู้ชาย
    while ($remainM > 0 && $roomIndex < count($rooms)) {
        $roomId = (int)$rooms[$roomIndex]['id'];
        $cap    = (int)$rooms[$roomIndex]['capacity'];
        $num    = min($cap, $remainM);

        $zero = 0;
        $insert->bind_param(
            'iissii',
            $bookingId,
            $roomId,
            $startDate,
            $endDate,
            $zero,
            $num
        );
        $insert->execute();

        $remainM   -= $num;
        $roomIndex += 1;
    }

    $insert->close();

    return $remainW === 0 && $remainM === 0;
}

/**
 * เติมรายชื่อผู้เข้าพักจาก booking_guest_requests
 * ลง room_guests ตาม room_allocations ของ booking นั้น
 *
 * ต้องถูกเรียกภายใต้ transaction เดียวกับ allocateRoomsStrict()
 */
function autoFillRoomGuestsFromRequests(mysqli $conn, int $bookingId): bool
{
    // 1) ดึง allocation ของ booking นี้
    $sqlAlloc = "
        SELECT 
            a.id AS allocation_id,
            a.room_id,
            a.woman_count,
            a.man_count
        FROM room_allocations a
        WHERE a.booking_id = ?
        ORDER BY a.id
    ";

    $stmt = $conn->prepare($sqlAlloc);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();

    $allocs = [];
    while ($row = $res->fetch_assoc()) {
        $allocs[(int)$row['allocation_id']] = $row;
    }
    $stmt->close();

    if (empty($allocs)) {
        // ยังไม่จัดสรรห้อง → ไม่มีอะไรให้เติม แต่ถือว่าไม่ error
        return true;
    }

    // 2) ดึงรายชื่อจาก booking_guest_requests
    $requests = getGuestRequestsByBookingId($conn, $bookingId);
    if (empty($requests)) {
        // ผู้จองไม่ได้กรอกชื่อไว้ → ข้าม (ให้ user ไปกรอกภายหลังได้)
        return true;
    }

    // แยกผู้หญิง / ผู้ชาย / ไม่ระบุ เพื่อง่ายต่อการจับคู่กับห้อง
    $women   = [];
    $men     = [];
    $unknown = [];

    foreach ($requests as $r) {
        $gender = strtoupper(trim((string)($r['gender'] ?? '')));

        if ($gender === 'F') {
            $women[] = $r;
        } elseif ($gender === 'M') {
            $men[] = $r;
        } else {
            $unknown[] = $r;
        }
    }

    // helper เล็ก ๆ สำหรับดึง guest ตัวถัดไปตามเพศที่ห้องต้องการ
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

    // 3) วนทีละ allocation แล้วอัดชื่อเข้า room_guests ตามจำนวนที่จัดคนไว้
    foreach ($allocs as $aid => $a) {
        $aid = (int)$aid;

        $womanCount = (int)($a['woman_count'] ?? 0);
        $manCount   = (int)($a['man_count']   ?? 0);
        $maxGuests  = $womanCount + $manCount;

        if ($maxGuests <= 0) {
            continue;
        }

        // กำหนดเพศหลักของห้อง (ใช้ logic เดิมจาก u_guest_form)
        $gender = ($womanCount > 0 && $manCount === 0) ? 'F' : 'M';

        // เคลียร์ room_guests เดิมของ allocation นี้ก่อน
        $del = $conn->prepare("
            DELETE FROM room_guests
            WHERE booking_id = ? AND allocation_id = ?
        ");
        if (!$del) {
            return false;
        }
        $del->bind_param('ii', $bookingId, $aid);
        $del->execute();
        $del->close();

        // เตรียม insert
        $ins = $conn->prepare("
            INSERT INTO room_guests (booking_id, allocation_id, guest_name, guest_phone, gender)
            VALUES (?, ?, ?, ?, ?)
        ");
        if (!$ins) {
            return false;
        }

        $inserted = 0;

        while ($inserted < $maxGuests) {
            $guest = $popGuest($gender);
            if ($guest === null) {
                break; // ไม่มี guest ให้ใส่แล้ว
            }

            $name  = trim((string)$guest['guest_name']);
            if ($name === '') {
                continue;
            }
            $phone = trim((string)($guest['guest_phone'] ?? ''));

            $ins->bind_param(
                'iisss',
                $bookingId,
                $aid,
                $name,
                $phone,
                $gender
            );
            $ins->execute();
            $inserted++;
        }

        $ins->close();
    }

    // ไม่ต้องเป๊ะ 100% ว่าครบทุกคน (เราตรวจแล้วตอนส่งฟอร์มอยู่แล้ว)
    return true;
}
