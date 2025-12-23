<?php
// repositories/documentRepo.php

function doc_listBookingsForDocumentPage(mysqli $conn): mysqli_result
{
    $sql = "
        SELECT b.*,
            (SELECT COUNT(*) FROM booking_documents d WHERE d.booking_id = b.id) AS doc_count
        FROM bookings b
        ORDER BY b.id DESC
    ";
    $res = $conn->query($sql);
    if (!$res) {
        throw new RuntimeException('doc_listBookingsForDocumentPage failed: ' . $conn->error);
    }
    return $res;
}

function doc_listByBookingId(mysqli $conn, int $bookingId): mysqli_result
{
    $stmt = $conn->prepare("
        SELECT *
        FROM booking_documents
        WHERE booking_id = ?
        ORDER BY uploaded_at DESC, id DESC
    ");
    if (!$stmt) {
        throw new RuntimeException('doc_listByBookingId prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    return $stmt->get_result(); // caller must not close stmt; use fetch loop then close in repo if you prefer
}

function doc_findById(mysqli $conn, int $docId): ?array
{
    $stmt = $conn->prepare("SELECT * FROM booking_documents WHERE id = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('i', $docId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function doc_insertAdminDocument(
    mysqli $conn,
    int $bookingId,
    string $docType,
    string $filePath,
    int $isVisibleToUser
): int {
    $uploadedBy = 'admin';

    $stmt = $conn->prepare("
        INSERT INTO booking_documents
            (booking_id, doc_type, file_path, uploaded_by, is_visible_to_user, uploaded_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    if (!$stmt) return 0;

    $stmt->bind_param('isssi', $bookingId, $docType, $filePath, $uploadedBy, $isVisibleToUser);
    $ok = $stmt->execute();
    $newId = $ok ? (int)$conn->insert_id : 0;
    $stmt->close();
    return $newId;
}

function doc_insertUserDocument(
    mysqli $conn,
    int $bookingId,
    string $docType,
    string $filePath
): int {
    $uploadedBy = 'user';
    $isVisibleToUser = 1;

    $stmt = $conn->prepare("
        INSERT INTO booking_documents
            (booking_id, doc_type, file_path, uploaded_by, is_visible_to_user, uploaded_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    if (!$stmt) return 0;

    $stmt->bind_param('isssi', $bookingId, $docType, $filePath, $uploadedBy, $isVisibleToUser);
    $ok = $stmt->execute();
    $newId = $ok ? (int)$conn->insert_id : 0;
    $stmt->close();
    return $newId;
}

function doc_deleteById(mysqli $conn, int $docId): bool
{
    $stmt = $conn->prepare("DELETE FROM booking_documents WHERE id = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('i', $docId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function doc_deleteByBookingId(mysqli $conn, int $bookingId): bool
{
    $stmt = $conn->prepare("DELETE FROM booking_documents WHERE booking_id = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $bookingId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function doc_listVisibleToUser(mysqli $conn, int $bookingId): mysqli_result
{
    $stmt = $conn->prepare("
        SELECT *
        FROM booking_documents
        WHERE booking_id = ?
          AND is_visible_to_user = 1
        ORDER BY uploaded_at DESC, id DESC
    ");
    if (!$stmt) {
        throw new RuntimeException('doc_listVisibleToUser prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    return $stmt->get_result();
}

function doc_listPathsByBookingId(mysqli $conn, int $bookingId): array
{
    $stmt = $conn->prepare("SELECT file_path FROM booking_documents WHERE booking_id = ?");
    if (!$stmt) return [];
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();

    $paths = [];
    while ($row = $res->fetch_assoc()) {
        if (!empty($row['file_path'])) $paths[] = $row['file_path'];
    }
    $stmt->close();
    return $paths;
}
