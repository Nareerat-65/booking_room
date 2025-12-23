<?php
// repositories/guestRequestRepo.php

function guestRequest_listByBookingId(mysqli $conn, int $bookingId): array
{
    $stmt = $conn->prepare("
        SELECT id, guest_name, gender, guest_phone
        FROM booking_guest_requests
        WHERE booking_id = ?
        ORDER BY id ASC
    ");
    if (!$stmt) return [];

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;

    $stmt->close();
    return $rows;
}

function guestRequest_replaceAll(mysqli $conn, int $bookingId, array $guests): void
{
    $del = $conn->prepare("DELETE FROM booking_guest_requests WHERE booking_id = ?");
    $del->bind_param('i', $bookingId);
    $del->execute();
    $del->close();

    if (empty($guests)) return;

    $ins = $conn->prepare("
        INSERT INTO booking_guest_requests (booking_id, guest_name, gender, guest_phone)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($guests as $g) {
        $name  = trim((string)($g['guest_name'] ?? ''));
        $gender = $g['gender'] ?? null;
        $phone = trim((string)($g['guest_phone'] ?? ''));

        if ($name === '') continue;
        if ($gender !== 'F' && $gender !== 'M') $gender = null;

        $ins->bind_param('isss', $bookingId, $name, $gender, $phone);
        $ins->execute();
    }

    $ins->close();
}
