<?php
// admin/requests/ad_updateStatus.php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/../../utils/admin_guard.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../services/bookingService.php';

$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = $_POST['status'] ?? '';
$reason = $_POST['reason'] ?? null;

if (!$id || !in_array($status, ['approved', 'rejected', 'pending'], true)) {
    http_response_code(400);
    echo 'invalid';
    exit;
}

if ($status === 'approved') {
    $booking = approveBooking($conn, $id);

    if ($booking) {
        sendBookingResultEmail($booking, 'approved', null);
        echo 'success';
    } else {
        echo 'error';
    }

} elseif ($status === 'rejected') {
    $booking = rejectBooking($conn, $id, (string)$reason);

    if ($booking) {
        sendBookingResultEmail($booking, 'rejected', (string)$reason);
        echo 'success';
    } else {
        echo 'error';
    }

} else { // pending
    $ok = setBookingPending($conn, $id);
    echo $ok ? 'success' : 'error';
}

$conn->close();
