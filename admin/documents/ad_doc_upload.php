<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once UTILS_PATH . '/admin_guard.php';
require_once CONFIG_PATH . '/db.php';

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
$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$mime     = $file['type'];
$size     = (int)$file['size'];
$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$storedName = uniqid('doc_') . ($ext ? ".$ext" : '');
$uploadDir  = __DIR__ . '/../../uploads/documents/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$fullPath = $uploadDir . $storedName;

$allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
$extLower = strtolower($ext);

if (!in_array($extLower, $allowedExtensions, true)) {
    header("Location: ad_doc_manage.php?booking_id={$booking_id}&error=type");
    exit;
}

$maxSize = 5 * 1024 * 1024; // 5 MB

if ($size > $maxSize) {
    header("Location: ad_doc_manage.php?booking_id={$booking_id}&error=size");
    exit;
}

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



$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisssssii",
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
