<?php
// repositories/bookingRepo.php

function booking_findById(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function booking_listAdmin(mysqli $conn): mysqli_result
{
    $sql = "
        SELECT b.*,
            (
                SELECT d.file_path
                FROM booking_documents d
                WHERE d.booking_id = b.id
                  AND d.uploaded_by = 'admin'
                  AND d.is_visible_to_user = 1
                ORDER BY d.uploaded_at DESC
                LIMIT 1
            ) AS admin_doc_path
        FROM bookings b
        ORDER BY b.id DESC
    ";

    $result = $conn->query($sql);
    if (!$result) {
        throw new RuntimeException('Query booking list failed: ' . $conn->error);
    }
    return $result;
}

function booking_updateByAdmin(
    mysqli $conn,
    int $bookingId,
    string $fullName,
    string $phone,
    string $lineId,
    string $email,
    ?string $position,
    ?int $studentYear,
    ?string $positionOther,
    string $department,
    ?string $purpose,
    string $studyCourse,
    string $studyDept,
    string $electiveDept,
    string $checkIn,
    string $checkOut,
    int $womanCount,
    int $manCount
): bool {
    $sql = "
        UPDATE bookings
        SET
            full_name      = ?,
            phone          = ?,
            line_id        = ?,
            email          = ?,
            position       = ?,
            student_year   = ?,
            position_other = ?,
            department     = ?,
            purpose        = ?,
            study_course   = ?,
            study_dept     = ?,
            elective_dept  = ?,
            check_in_date  = ?,
            check_out_date = ?,
            woman_count    = ?,
            man_count      = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;

    $stmt->bind_param(
        'sssssissssssssiii',
        $fullName,
        $phone,
        $lineId,
        $email,
        $position,
        $studentYear,
        $positionOther,
        $department,
        $purpose,
        $studyCourse,
        $studyDept,
        $electiveDept,
        $checkIn,
        $checkOut,
        $womanCount,
        $manCount,
        $bookingId
    );

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function booking_setApproved(mysqli $conn, int $bookingId): bool
{
    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'approved', reject_reason = NULL
        WHERE id = ?
    ");
    if (!$stmt) return false;
    $stmt->bind_param('i', $bookingId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function booking_setRejected(mysqli $conn, int $bookingId, string $reason): bool
{
    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'rejected', reject_reason = ?
        WHERE id = ?
    ");
    if (!$stmt) return false;
    $stmt->bind_param('si', $reason, $bookingId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function booking_setPending(mysqli $conn, int $bookingId): bool
{
    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'pending', reject_reason = NULL
        WHERE id = ?
    ");
    if (!$stmt) return false;
    $stmt->bind_param('i', $bookingId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function booking_deleteById(mysqli $conn, int $bookingId): int
{
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}
