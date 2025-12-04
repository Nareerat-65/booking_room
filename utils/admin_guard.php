<?php
// admin/admin_guard.php

// ให้แน่ใจว่าเริ่ม session แล้ว (กันซ้ำ)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ถ้ายังไม่มี admin_id ให้เด้งไปหน้า login
if (empty($_SESSION['admin_id'])) {
    // ปรับ path ตรงนี้ให้ตรงกับของจริงในโปรเจกต์คุณ
    header("Location: /admin/ad_login.php");
    exit;
}
?>