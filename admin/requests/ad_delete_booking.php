<?php

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/../../utils/admin_guard.php';
require_once __DIR__ . '/../../db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo 'invalid';
    exit;
}

// ถ้าตารางลูก (room_allocations, booking_documents, room_guests) มี FK ON DELETE CASCADE
// การลบ bookings แถวนี้จะลบของที่เกี่ยวข้องให้อัตโนมัติ
$sql = "DELETE FROM bookings WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo 'error';
    exit;
}

$stmt->bind_param('i', $id);
$ok = $stmt->execute();
$stmt->close();

echo $ok ? 'success' : 'error';

$conn->close();
