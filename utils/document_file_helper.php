<?php
// utils/document_file_helper.php

/**
 * รับ path ที่เก็บใน DB (เช่น "uploads/documents/xxx.pdf")
 * คืน path แบบเต็มในเครื่อง หรือ null ถ้า path ไม่ปลอดภัย / ไม่มีไฟล์
 */
function resolveDocumentFullPath(string $relativePath): ?string
{
    if (!$relativePath || preg_match('#^https?://#', $relativePath)) {
        // เป็น URL หรือว่าง → ให้ handle นอกฟังก์ชัน
        return null;
    }

    $projectRoot = realpath(__DIR__ . '/..');  // ชี้ไป root โปรเจกต์
    $fullPath    = realpath($projectRoot . '/' . ltrim($relativePath, '/'));

    if (!$fullPath) {
        return null;
    }

    // กัน path ทะลุออกนอกโปรเจกต์
    if (strpos($fullPath, $projectRoot) !== 0) {
        return null;
    }

    if (!is_file($fullPath)) {
        return null;
    }

    return $fullPath;
}

/** ลบไฟล์ในเครื่องถ้าไม่ใช่ URL */
function deleteDocumentFileIfLocal(string $relativePath): void
{
    $fullPath = resolveDocumentFullPath($relativePath);
    if ($fullPath) {
        @unlink($fullPath);
    }
}
