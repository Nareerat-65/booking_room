<?php
// user/u_upload_document_process.php

session_start();
require_once dirname(__DIR__, 1) . '/config.php';
require_once CONFIG_PATH . '/db.php';
require_once SERVICES_PATH . '/documentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: u_upload_document.php');
    exit;
}

$bookingId = (int)($_POST['booking_id'] ?? 0);

if ($bookingId <= 0) {
    header('Location: u_upload_document.php?error=' . urlencode('ไม่พบคำขอจองห้องพักที่ต้องการ'));
    exit;
}

// ตรวจว่า booking นี้มีจริงไหม (กันยิงสุ่ม id)
$stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    header('Location: u_upload_document.php?error=' . urlencode('ไม่พบข้อมูลการจองห้องพักในระบบ'));
    exit;
}

// โฟลเดอร์เก็บไฟล์ (อยู่โฟลเดอร์หลักของโปรเจกต์)
$uploadDir       = __DIR__ . '/../uploads/documents/';
$relativeBaseDir = 'uploads/documents/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// กำหนดรูปแบบไฟล์ที่อนุญาต
$allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
$maxSize    = 5 * 1024 * 1024; // 5 MB

$files = $_FILES['documents'] ?? null;

if (!$files || !is_array($files['name']) || count($files['name']) === 0) {
    header('Location: u_upload_document.php?booking_id=' . $bookingId . '&error=' . urlencode('กรุณาเลือกไฟล์อย่างน้อย 1 ไฟล์'));
    exit;
}

$total = count($files['name']);
$uploadedCount = 0;

for ($i = 0; $i < $total; $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }

    $originalName = $files['name'][$i];
    $tmpName      = $files['tmp_name'][$i];
    $size         = (int)$files['size'][$i];

    if ($size <= 0 || $size > $maxSize) {
        continue;
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        continue;
    }

    // map mime type จากนามสกุลไฟล์ (ไม่ใช้ finfo_open กัน error)
    $mimeMap = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];
    $mime = $mimeMap[$ext] ?? 'application/octet-stream';

    // สร้างชื่อไฟล์ใหม่กันซ้ำ
    $newName  = 'booking_' . $bookingId . '_user_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    $fullPath = $uploadDir . $newName;

    if (!move_uploaded_file($tmpName, $fullPath)) {
        continue;
    }

    $relativePath = $relativeBaseDir . $newName;
    $fileSize     = $size;

    // ใช้ service บันทึกข้อมูลเอกสาร
    if (insertUserDocument($conn, $bookingId, $originalName, $newName, $relativePath, $mime, $fileSize)) {
        $uploadedCount++;
    } else {
        @unlink($fullPath);
    }
}

// กลับไปหน้าเดิม พร้อมสถานะ
if ($uploadedCount > 0) {
    header('Location: u_upload_document.php?booking_id=' . $bookingId . '&msg=uploaded');
} else {
    header('Location: u_upload_document.php?booking_id=' . $bookingId . '&error=' . urlencode('ไม่สามารถอัปโหลดเอกสารได้ กรุณาลองใหม่อีกครั้ง'));
}
exit;
