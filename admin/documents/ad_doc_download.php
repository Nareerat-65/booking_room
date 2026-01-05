<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once UTILS_PATH . '/admin_guard.php';
require_once CONFIG_PATH . '/db.php';
require_once SERVICES_PATH . '/documentService.php';
require_once UTILS_PATH . '/document_file_helper.php';

// 1) รับ id เอกสารจาก query string
$doc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($doc_id <= 0) {
    http_response_code(400);
    echo "Invalid document id.";
    exit;
}

// 2) ดึงข้อมูลเอกสารจาก DB
$doc = getDocumentById($conn, $doc_id);
if (!$doc) {
    http_response_code(404);
    echo "Document not found.";
    exit;
}

// 3) ดึง path จาก DB
$filePath = $doc['file_path'] ?? '';
if ($filePath === '') {
    http_response_code(404);
    echo "File path is empty.";
    exit;
}

// 4) ถ้าเป็น URL (เก็บบน Cloud/ที่อื่น) ให้ redirect ออกไปเลย
if (preg_match('#^https?://#', $filePath)) {
    header("Location: " . $filePath);
    exit;
}

// 5) ถ้าเป็นไฟล์ในเครื่อง → แปลงเป็น path เต็มแล้วเช็คว่ามีอยู่จริง
$fullPath = resolveDocumentFullPath($filePath);
if (!$fullPath) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// 6) เตรียมข้อมูล header
$mime         = $doc['mime_type']    ?? 'application/octet-stream';
$originalName = $doc['original_name'] ?? basename($fullPath);

// เคลียร์ output buffer เดิม (กัน header พัง)
if (ob_get_level()) {
    ob_end_clean();
}

// 7) ส่ง header และไฟล์ออกไป
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($originalName) . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($fullPath);
exit;
