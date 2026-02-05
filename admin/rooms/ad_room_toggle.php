<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once CONFIG_PATH . '/db.php';
require_once UTILS_PATH . '/admin_guard.php';

$id     = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id <= 0 || !in_array($action, ['on', 'off'], true)) {
    header("Location: ad_rooms_list.php");
    exit;
}

// ดึงข้อมูลห้อง
$stmt = $conn->prepare("SELECT is_active FROM rooms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    header("Location: ad_rooms_list.php");
    exit;
}

// ถ้าจะปิดห้อง → เช็คว่ามีคนพักอยู่ปัจจุบันไหม
if ($action === 'off') {

    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(woman_count + man_count), 0)
         FROM room_allocations
         WHERE room_id = ?
         AND CURDATE() BETWEEN start_date AND end_date"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($current_guest_count);
    $stmt->fetch();
    $stmt->close();

    if ((int)$current_guest_count > 0) {
        // มีคนพักอยู่ → ห้ามปิด
        header("Location: ad_rooms_list.php?error=room_has_guest");
        exit;
    }
}

// toggle สถานะ
$is_active = ($action === 'on') ? 1 : 0;

$stmt = $conn->prepare(
    "UPDATE rooms SET is_active = ? WHERE id = ?"
);
$stmt->bind_param("ii", $is_active, $id);
$stmt->execute();

header("Location: ad_rooms_list.php?success=room_updated");
exit;
