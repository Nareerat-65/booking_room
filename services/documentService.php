<?php
// services/documentService.php

require_once __DIR__ . '/../repositories/documentRepo.php';

function getBookingsForDocumentPage(mysqli $conn): mysqli_result
{
    return doc_listBookingsForDocumentPage($conn);
}

function getDocumentsByBookingId(mysqli $conn, int $bookingId): mysqli_result
{
    return doc_listByBookingId($conn, $bookingId);
}

function getDocumentById(mysqli $conn, int $docId): ?array
{
    return doc_findById($conn, $docId);
}

function insertBookingDocument(
    mysqli $conn,
    int $bookingId,
    string $docType,
    string $filePath,
    int $isVisibleToUser = 1
): int {
    return doc_insertAdminDocument($conn, $bookingId, $docType, $filePath, $isVisibleToUser);
}

function deleteBookingDocumentById(mysqli $conn, int $docId): bool
{
    return doc_deleteById($conn, $docId);
}

function getDocumentsVisibleToUser(mysqli $conn, int $bookingId): mysqli_result
{
    return doc_listVisibleToUser($conn, $bookingId);
}

function insertUserDocument(mysqli $conn, int $bookingId, string $docType, string $filePath): int
{
    return doc_insertUserDocument($conn, $bookingId, $docType, $filePath);
}
