<?php
// utils/booking_service.php

/**
 * ดึงรายการจองทั้งหมดสำหรับหน้า admin/requests
 * คืนค่าเป็น mysqli_result (เอาไป fetch_assoc() ต่อในหน้า view ได้เลย)
 */
function getAdminBookingList(mysqli $conn): mysqli_result
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
        // เผื่อ debug
        throw new RuntimeException('Query booking list failed: ' . $conn->error);
    }

    return $result;
}
