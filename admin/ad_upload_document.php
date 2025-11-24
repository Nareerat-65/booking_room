<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ad_login.php');
    exit;
}

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ad_requests.php');
    exit;
}

$bookingId = (int)($_POST['booking_id'] ?? 0);

if ($bookingId <= 0 || !isset($_FILES['document'])) {
    header('Location: ad_requests.php?msg=invalid');
    exit;
}

// โฟลเดอร์เก็บไฟล์
$uploadDir = __DIR__ . '/../uploads/documents/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file    = $_FILES['document'];
$allowed = ['pdf', 'jpg', 'jpeg', 'png'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: ad_requests.php?msg=upload_error');
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    header('Location: ad_requests.php?msg=invalid_type');
    exit;
}

if ($file['size'] > $maxSize) {
    header('Location: ad_requests.php?msg=too_big');
    exit;
}

// ตั้งชื่อไฟล์ใหม่
$newName = 'booking_' . $bookingId . '_' . time() . '.' . $ext;
$dest    = $uploadDir . $newName;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    header('Location: ad_requests.php?msg=move_failed');
    exit;
}

// path ที่เก็บในฐานข้อมูล (relative)
$relativePath = 'uploads/documents/' . $newName;

$check = $conn->prepare("SELECT status FROM bookings WHERE id=?");
$check->bind_param("i", $bookingId);
$check->execute();
$statusRow = $check->get_result()->fetch_assoc();
$check->close();

if (($statusRow['status'] ?? '') !== 'approved') {
    header('Location: ad_requests.php?msg=not_approved');
    exit;
}

// อย่าลืมเพิ่มคอลัมน์ เช่น bookings.document_path ใน DB ก่อนใช้งาน
$stmt = $conn->prepare("UPDATE bookings SET document_path = ? WHERE id = ?");
$stmt->bind_param('si', $relativePath, $bookingId);
$stmt->execute();
$stmt->close();

header('Location: ad_requests.php?msg=uploaded');
exit;
