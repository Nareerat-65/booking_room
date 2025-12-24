<?php
require_once __DIR__ . '/../db.php';

/**
 * ดึงรายการ booking ที่อนุมัติแล้ว + นับจำนวนไฟล์เอกสาร
 * ใช้แทน SQL ใน ad_doc_bookings.php
 */
function getBookingsForDocumentPage(mysqli $conn): mysqli_result
{
    $sql = "
        SELECT 
            b.id,
            b.full_name,
            b.department,
            b.check_in_date,
            b.check_out_date,
            b.status,
            COUNT(d.id) AS doc_count
        FROM bookings b
        LEFT JOIN booking_documents d ON d.booking_id = b.id
        WHERE b.status = 'approved' 
        GROUP BY b.id
        ORDER BY b.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result();
}

/** ดึงเอกสารทั้งหมดของ booking หนึ่งใบ */
function getDocumentsByBookingId(mysqli $conn, int $bookingId): mysqli_result
{
    $sql = "
        SELECT d.*, 
               CASE 
                 WHEN d.uploaded_by = 'admin' THEN a.full_name
                 ELSE d.uploaded_by
               END AS uploader_name
        FROM booking_documents d
        LEFT JOIN admins a
          ON d.uploaded_by = 'admin' AND d.uploader_id = a.id
        WHERE d.booking_id = ?
        ORDER BY d.uploaded_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    return $stmt->get_result();
}

/** ดึงข้อมูลเอกสารตาม id */
function getDocumentById(mysqli $conn, int $docId): ?array
{
    $sql = "SELECT * FROM booking_documents WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $docId);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc() ?: null;
}



function insertAdminDocument(
    mysqli $conn,
    int $bookingId,
    int $adminId,
    string $docType,
    string $originalName,
    string $storedName,
    string $dbPath,
    string $mime,
    int $size,
    int $isVisible
): bool {
    $sql = "
        INSERT INTO booking_documents
            (booking_id, uploaded_by, uploader_id, doc_type,
             original_name, stored_name, file_path, mime_type, file_size,
             is_visible_to_user, uploaded_at)
        VALUES (?, 'admin', ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iisssssii",
        $bookingId,
        $adminId,
        $docType,
        $originalName,
        $storedName,
        $dbPath,
        $mime,
        $size,
        $isVisible
    );

    return $stmt->execute();
}

function insertUserDocument(
    mysqli $conn,
    int $bookingId,
    string $originalName,
    string $storedName,
    string $relativePath,
    string $mime,
    int $fileSize
): bool {
    $sql = "INSERT INTO booking_documents
            (booking_id, uploaded_by, uploader_id, doc_type,
             original_name, stored_name, file_path, mime_type, file_size,
             is_visible_to_user, uploaded_at)
            VALUES (?, 'user', 0, 'user_attachment',
                    ?, ?, ?, ?, ?, 1, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'issssi',
        $bookingId,
        $originalName,
        $storedName,
        $relativePath,
        $mime,
        $fileSize
    );

    return $stmt->execute();
}
/** ลบ row เอกสารใน DB */
function deleteBookingDocumentById(mysqli $conn, int $docId): bool
{
    $sql = "DELETE FROM booking_documents WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $docId);
    return $stmt->execute();
}

function getDocumentsVisibleToUser(mysqli $conn, int $bookingId): mysqli_result
{
    $sql = "
        SELECT id, uploaded_by, doc_type, original_name, file_path, mime_type, file_size, uploaded_at
        FROM booking_documents
        WHERE booking_id = ?
          AND is_visible_to_user = 1
        ORDER BY uploaded_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    return $stmt->get_result();
}

