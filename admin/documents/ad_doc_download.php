<?php
// admin/documents/ad_doc_download.php

require_once __DIR__ . '/../../utils/admin_guard.php';
require_once '../../db.php';  // ใช้ $conn แบบเดียวกับ ad_doc_manage.php

$doc_id = (int)($_GET['id'] ?? 0);

if ($doc_id <= 0) {
    http_response_code(400);
    echo "Invalid document id.";
    exit;
}

// 1) ดึงข้อมูลเอกสารจากฐานข้อมูล
$stmt = $conn->prepare("
    SELECT original_name, file_path, mime_type 
    FROM booking_documents 
    WHERE id = ?
");
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$result = $stmt->get_result();
$doc = $result->fetch_assoc();
$stmt->close();

if (!$doc) {
    http_response_code(404);
    echo "Document not found.";
    exit;
}

$originalName = $doc['original_name'] ?: 'document';
$filePath     = $doc['file_path'];
$mime         = $doc['mime_type'] ?: 'application/octet-stream';

// ถ้าเป็น URL (เช่น เก็บใน cloud) ให้ redirect ไปเลย
if (preg_match('#^https?://#', $filePath)) {
    header("Location: " . $filePath);
    exit;
}

// 2) ถ้าเป็นไฟล์ในเครื่อง ให้ต่อ path จริง
$projectRoot = realpath(__DIR__ . '/../../');              // โฟลเดอร์โปรเจกต์หลัก
$fullPath    = realpath($projectRoot . '/' . ltrim($filePath, '/'));

if (!$fullPath || strpos($fullPath, $projectRoot) !== 0 || !is_file($fullPath)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// 3) ส่ง header แล้วอ่านไฟล์ออกไป
// ป้องกัน output เก่าค้าง
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($originalName) . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($fullPath);
exit;
