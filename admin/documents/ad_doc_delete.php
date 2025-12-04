<?php
// admin/documents/ad_doc_delete.php

require_once __DIR__ . '/../../utils/admin_guard.php';
require_once '../../db.php';

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

// 1) ดึงข้อมูลเอกสารมาก่อน เพื่อรู้ path ไฟล์
$stmt = $conn->prepare("SELECT file_path FROM booking_documents WHERE id = ?");
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$result = $stmt->get_result();
$doc    = $result->fetch_assoc();
$stmt->close();

if ($doc) {
    $relativePath = $doc['file_path'];   // เช่น "uploads/documents/xxx.pdf"

    if (!empty($relativePath) && !preg_match('#^https?://#', $relativePath)) {
        // ต่อ path แบบชี้จาก root โปรเจกต์
        $projectRoot = realpath(__DIR__ . '/../../');                      // โฟลเดอร์โปรเจกต์หลัก
        $fullPath    = realpath($projectRoot . '/' . ltrim($relativePath, '/'));

        // กันพลาดไม่ให้ไปลบไฟล์นอกโปรเจกต์
        if ($fullPath && strpos($fullPath, $projectRoot) === 0 && is_file($fullPath)) {
            @unlink($fullPath);   // ลบไฟล์จริง
        }
    }

    // 2) ลบ row ในฐานข้อมูล
    $del = $conn->prepare("DELETE FROM booking_documents WHERE id = ?");
    $del->bind_param("i", $doc_id);
    $del->execute();
    $del->close();
}

// 3) กลับไปหน้า manage เดิม
if ($booking_id > 0) {
    header("Location: ad_doc_manage.php?booking_id={$booking_id}");
} else {
    header("Location: ad_doc_bookings.php");
}
exit;
