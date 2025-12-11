<?php
// admin/requests/ad_delete_booking.php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/../../utils/admin_guard.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../services/bookingService.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo 'invalid';
    exit;
}

$ok = deleteBooking($conn, $id);

echo $ok ? 'success' : 'error';
