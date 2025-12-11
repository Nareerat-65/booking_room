<?php
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