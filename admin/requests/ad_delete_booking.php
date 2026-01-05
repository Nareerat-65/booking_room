<?php
// admin/requests/ad_delete_booking.php
header('Content-Type: text/plain; charset=utf-8');

require_once dirname(__DIR__, 2) . '/config.php';
require_once UTILS_PATH . '/admin_guard.php';
require_once CONFIG_PATH . '/db.php';
require_once SERVICES_PATH . '/bookingService.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo 'invalid';
    exit;
}

$ok = deleteBooking($conn, $id);

echo $ok ? 'success' : 'error';
