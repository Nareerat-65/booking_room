<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ad_login.php");
    exit;
}

require_once dirname(__DIR__, 2) . '/config.php';
require_once CONFIG_PATH . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ad_change_password.php");
    exit;
}

$adminId = (int)$_SESSION['admin_id'];
$oldPass = trim($_POST['old_password'] ?? '');
$newPass = trim($_POST['new_password'] ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

// 1) เช็ค confirm ตรงกันไหม
if ($newPass !== $confirm) {
    header("Location: ad_change_password.php?msg=mismatch");
    exit;
}

// 2) เช็คความยาวรหัสผ่านใหม่ (ปรับได้)
if (strlen($newPass) < 6) {
    header("Location: ad_change_password.php?msg=too_short");
    exit;
}

// ดึง hash เดิม
$stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

// ตรวจรหัสผ่านเดิม
if (!password_verify($oldPass, $row['password'])) {
    header("Location: ad_change_password.php?msg=old_wrong");
    exit;
}

// สร้าง hash ใหม่ด้วย bcrypt
$newHash = password_hash($newPass, PASSWORD_DEFAULT);

$upd = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
$upd->bind_param("si", $newHash, $adminId);
$upd->execute();
$upd->close();

header("Location: ad_change_password.php?msg=ok");
exit;

$conn->close();

if ($ok) {
    header("Location: ad_change_password.php?msg=ok");
} else {
    header("Location: ad_change_password.php?msg=error");
}
exit;
