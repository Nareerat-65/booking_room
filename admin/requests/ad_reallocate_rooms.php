<?php
require_once __DIR__ . '/../../utils/admin_guard.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../services/bookingService.php';
require_once __DIR__ . '/../../services/roomAllocationService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ad_requests.php?error=invalid_method');
    exit;
}

$bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
if ($bookingId <= 0) {
    header('Location: ad_requests.php?error=invalid_booking_id');
    exit;
}

$booking = getBookingById($conn, $bookingId);
if (!$booking) {
    header('Location: ad_requests.php?error=booking_not_found');
    exit;
}

if (($booking['status'] ?? 'pending') !== 'approved') {
    header("Location: ad_requests.php?error=not_approved");
    exit;
}

try {
    $conn->begin_transaction();

    // ลบห้องเดิม
    $stmt = $conn->prepare("DELETE FROM room_allocations WHERE booking_id = ?");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $stmt->close();

    // (ถ้าทำตามข้อแนะนำในข้อ 1 แล้ว autoFill จะลบ room_guests เอง
    //  แต่จะลบตรงนี้เลยก็ได้ ชัดเจนดี)
    $stmt = $conn->prepare("DELETE FROM room_guests WHERE booking_id = ?");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $stmt->close();

    // จัดใหม่
    $ok = allocateRoomsStrict($conn, $bookingId);
    if (!$ok) {
        $conn->rollback();
        header("Location: ad_booking_edit.php?id={$bookingId}&reallocate_error=full");
        exit;
    }

    // ✅ เติมรายชื่อผู้เข้าพักลง room_guests ตาม allocation ใหม่
    if (!autoFillRoomGuestsFromRequests($conn, $bookingId)) {
        $conn->rollback();
        header("Location: ad_booking_edit.php?id={$bookingId}&reallocate_error=guest_fill");
        exit;
    }

    $conn->commit();
    header("Location: ad_booking_edit.php?id={$bookingId}&reallocated=1");
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    error_log('reallocate error: ' . $e->getMessage());
    header("Location: ad_booking_edit.php?id={$bookingId}&reallocate_error=exception");
    exit;
}
