<?php
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if (!$id || !$status) {
        http_response_code(400);
        echo "Missing required fields";
        exit;
    }

    // อัปเดตสถานะและเหตุผล (กรณีไม่อนุมัติ)
    $sql = "UPDATE bookings SET status = ?, reject_reason = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $status, $reason, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>