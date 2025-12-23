<?php
// repositories/roomAllocationRepo.php

/**
 * ดึงข้อมูลที่จำเป็นสำหรับจัดห้องจาก bookings
 * return ['woman_count'=>int, 'man_count'=>int, 'check_in_date'=>string, 'check_out_date'=>string] หรือ null
 */
function alloc_getBookingCountsAndDates(mysqli $conn, int $bookingId): ?array
{
    $stmt = $conn->prepare("
        SELECT woman_count, man_count, check_in_date, check_out_date
        FROM bookings
        WHERE id = ?
        LIMIT 1
    ");
    if (!$stmt) return null;

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

/**
 * หา rooms ที่ว่างในช่วง startDate - endDate
 * (ยึด logic เดิมของคุณ: room ว่างถ้าไม่มี allocation ที่ช่วงวันทับกัน โดย buffer end_date + 3 วัน)
 * return array of ['id'=>..,'capacity'=>..]
 */
function alloc_findAvailableRoomsWithBuffer(mysqli $conn, string $startDate, string $endDate): array
{
    $sql = "
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

    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];

    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $res = $stmt->get_result();

    $rooms = [];
    while ($row = $res->fetch_assoc()) {
        $rooms[] = $row;
    }
    $stmt->close();

    return $rooms;
}

/**
 * เพิ่ม allocation 1 แถว
 */
function alloc_insertAllocation(
    mysqli $conn,
    int $bookingId,
    int $roomId,
    string $startDate,
    string $endDate,
    int $womanCount,
    int $manCount
): bool {
    $stmt = $conn->prepare("
        INSERT INTO room_allocations
            (booking_id, room_id, start_date, end_date, woman_count, man_count)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) return false;

    $stmt->bind_param('iissii', $bookingId, $roomId, $startDate, $endDate, $womanCount, $manCount);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/**
 * ลบ allocations ของ booking (ใช้ตอน approve ก่อนจัดใหม่)
 */
function alloc_deleteByBookingId(mysqli $conn, int $bookingId): bool
{
    $stmt = $conn->prepare("DELETE FROM room_allocations WHERE booking_id = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $bookingId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/**
 * ดึง allocations ของ booking (ใช้ตอนเติมชื่อผู้เข้าพัก)
 * return array of ['allocation_id'=>..,'room_id'=>..,'woman_count'=>..,'man_count'=>..]
 */
function alloc_listAllocationsByBookingId(mysqli $conn, int $bookingId): array
{
    $stmt = $conn->prepare("
        SELECT
            a.id AS allocation_id,
            a.room_id,
            a.woman_count,
            a.man_count
        FROM room_allocations a
        WHERE a.booking_id = ?
        ORDER BY a.id ASC
    ");
    if (!$stmt) return [];

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    $stmt->close();

    return $rows;
}

/**
 * ลบ room_guests ของ booking + allocation
 */
function guest_deleteByBookingAndAllocation(mysqli $conn, int $bookingId, int $allocationId): bool
{
    $stmt = $conn->prepare("
        DELETE FROM room_guests
        WHERE booking_id = ? AND allocation_id = ?
    ");
    if (!$stmt) return false;

    $stmt->bind_param('ii', $bookingId, $allocationId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/**
 * เพิ่ม room_guest (ผูกกับ allocation_id)
 */
function guest_insertByAllocation(
    mysqli $conn,
    int $bookingId,
    int $allocationId,
    string $guestName,
    string $guestPhone,
    string $gender
): bool {
    $stmt = $conn->prepare("
        INSERT INTO room_guests (booking_id, allocation_id, guest_name, guest_phone, gender)
        VALUES (?, ?, ?, ?, ?)
    ");
    if (!$stmt) return false;

    $stmt->bind_param('iisss', $bookingId, $allocationId, $guestName, $guestPhone, $gender);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
