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
$adminId   = (int)($_SESSION['admin_id'] ?? 0);

if ($bookingId <= 0 || !isset($_FILES['document'])) {
    header('Location: ad_requests.php?msg=invalid');
    exit;
}

// 1) เช็คว่า booking นี้อนุมัติแล้วหรือยัง
$check = $conn->prepare("SELECT status FROM bookings WHERE id = ?");
$check->bind_param("i", $bookingId);
$check->execute();
$statusRow = $check->get_result()->fetch_assoc();
$check->close();

if (($statusRow['status'] ?? '') !== 'approved') {
    header('Location: ad_requests.php?msg=not_approved');
    exit;
}

// 2) เตรียมโฟลเดอร์เก็บไฟล์
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

// นามสกุลไฟล์
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    header('Location: ad_requests.php?msg=invalid_type');
    exit;
}

if ($file['size'] > $maxSize || $file['size'] <= 0) {
    header('Location: ad_requests.php?msg=too_big');
    exit;
}

// 3) ตั้งชื่อไฟล์ใหม่ + ย้ายไฟล์
$originalName = $file['name'];
$newName      = 'booking_' . $bookingId . '_' . time() . '.' . $ext;
$dest         = $uploadDir . $newName;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    header('Location: ad_requests.php?msg=move_failed');
    exit;
}

// path สำหรับเก็บใน DB (relative)
$relativePath = 'uploads/documents/' . $newName;

// 4) หา MIME type แบบง่าย (จากนามสกุลไฟล์)
$mimeMap = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];
$mime = $mimeMap[$ext] ?? ($file['type'] ?: 'application/octet-stream');

$fileSize = (int)$file['size'];

// 5) INSERT ลง booking_documents (admin อัปเอกสารตอบกลับ)
$sql = "INSERT INTO booking_documents
        (booking_id, uploaded_by, uploader_id, doc_type,
         original_name, stored_name, file_path, mime_type, file_size,
         is_visible_to_user, uploaded_at)
        VALUES (?, 'admin', ?, 'admin_reply',
                ?, ?, ?, ?, ?, 1, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'iissssi',
    $bookingId,
    $adminId,
    $originalName,
    $newName,
    $relativePath,
    $mime,
    $fileSize
);

if (!$stmt->execute()) {
    // ถ้า insert DB ไม่ผ่าน ลบไฟล์ทิ้ง
    @unlink($dest);
    header('Location: ad_requests.php?msg=db_error');
    exit;
}

$stmt->close();

header('Location: ad_requests.php?msg=uploaded');
exit;
