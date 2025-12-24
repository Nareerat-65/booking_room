<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils/booking_helper.php';

/**
 * นับจำนวนวันแบบรวมวันเข้า + วันออก
 */
function diffDaysInclusive(string $checkIn, string $checkOut): int
{
    $in  = new DateTime($checkIn);
    $out = new DateTime($checkOut);
    return (int)$in->diff($out)->days + 1;
}

/**
 * ดึงข้อมูลที่จำเป็นสำหรับออกบิล
 */
function getBillData(mysqli $conn, int $bookingId): array
{
    // booking หลัก
    $booking = getBookingById($conn, $bookingId);
    if (!$booking) {
        throw new Exception('ไม่พบข้อมูลการจอง');
    }

    // นับจำนวนห้อง
    $sqlRoom = "
        SELECT COUNT(DISTINCT room_id) AS room_count
        FROM room_allocations
        WHERE booking_id = ?
    ";
    $stmt = $conn->prepare($sqlRoom);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $roomCount = (int)($stmt->get_result()->fetch_assoc()['room_count'] ?? 0);
    $stmt->close();

    // จำนวนผู้เข้าพัก
    $people = (int)($booking['woman_count'] ?? 0) + (int)($booking['man_count'] ?? 0);

    return [
        'booking'    => $booking,
        'room_count' => max(1, $roomCount),
        'people'     => max(1, $people),
    ];
}

/**
 * คำนวณค่าใช้จ่าย
 */
function calculateBill(array $booking, int $roomCount, int $people): array
{
    $days = diffDaysInclusive(
        $booking['check_in_date'],
        $booking['check_out_date']
    );

    // บุคคลภายนอก
    if (($booking['position'] ?? '') === 'other') {
        $rate  = 150;
        $total = $days * $rate * $people;

        return [
            'type'   => 'external',
            'rate'   => $rate,
            'days'   => $days,
            'units'  => $people,
            'total'  => $total,
            'label'  => '150 บาท / คน / วัน'
        ];
    }

    // บุคลากรภายใน
    $rate  = 35;
    $total = $days * $rate * $roomCount;

    return [
        'type'   => 'internal',
        'rate'   => $rate,
        'days'   => $days,
        'units'  => $roomCount,
        'total'  => $total,
        'label'  => '35 บาท / ห้อง / วัน'
    ];
}
