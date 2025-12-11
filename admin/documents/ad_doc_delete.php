<?php
require_once __DIR__ . '/../../utils/admin_guard.php';
require_once '../../db.php';
require_once __DIR__ . '/../../services/documentService.php';
require_once __DIR__ . '/../../utils/document_file_helper.php';

// รับค่าจากลิงก์
$doc_id     = (int)($_GET['id'] ?? 0);
$booking_id = (int)($_GET['booking_id'] ?? 0);

// ถ้าไม่มี id เลย ให้เด้งกลับ
if ($doc_id <= 0) {
    if ($booking_id > 0) {
        header("Location: ad_doc_manage.php?booking_id={$booking_id}");
    } else {
        header("Location: ad_doc_bookings.php");
    }
    exit;
}

$doc = getDocumentById($conn, $doc_id);

if ($doc) {
    // ลบไฟล์จริง (ถ้าเป็น local)
    if (!empty($doc['file_path'])) {
        deleteDocumentFileIfLocal($doc['file_path']);
    }

    // ลบ row DB
    deleteBookingDocumentById($conn, $doc_id);
}

// 3) กลับไปหน้า manage เดิม
if ($booking_id > 0) {
    header("Location: ad_doc_manage.php?booking_id={$booking_id}");
} else {
    header("Location: ad_doc_bookings.php");
}
exit;
