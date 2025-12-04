<?php
require_once __DIR__ . '/../../utils/admin_guard.php';
require_once '../../db.php';

$booking_id = (int)($_POST['booking_id'] ?? 0);
$doc_type   = trim($_POST['doc_type'] ?? '');
$is_visible = isset($_POST['is_visible_to_user']) ? (int)$_POST['is_visible_to_user'] : 1;

if ($booking_id <= 0 || empty($_FILES['file']['name'])) {
    header("Location: ad_doc_bookings.php");
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    header("Location: ad_doc_manage.php?booking_id={$booking_id}&error=upload");
    exit;
}

$originalName = $file['name'];
$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$storedName = uniqid('doc_') . ($ext ? ".$ext" : '');
$uploadDir  = __DIR__ . '/../../uploads/documents/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$fullPath = $uploadDir . $storedName;

if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
    header("Location: ad_doc_manage.php?booking_id={$booking_id}&error=save");
    exit;
}

// เก็บเฉพาะ path relative ไว้ใน DB เช่น "uploads/documents/xxxx.pdf"
$dbPath = 'uploads/documents/' . $storedName;

$sql = "
    INSERT INTO booking_documents
        (booking_id, uploaded_by, uploader_id, doc_type,
         original_name, stored_name, file_path, mime_type, file_size, is_visible_to_user, uploaded_at)
    VALUES (?, 'admin', ?, ?, ?, ?, ?, ?, ?, ?, NOW())
";

$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$mime     = $file['type'];
$size     = (int)$file['size'];

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisssssii", 
    $booking_id,
    $admin_id,
    $doc_type,
    $originalName,
    $storedName,
    $dbPath,
    $mime,
    $size,
    $is_visible
);

$stmt->execute();

header("Location: ad_doc_manage.php?booking_id={$booking_id}");
exit;
