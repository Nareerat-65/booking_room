<?php
session_start();
require_once '../db.php';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {

            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['full_name'];

            header('Location: ad_dashboard.php');
            exit;
        } else {
            $error = 'รหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $error = 'ไม่พบบัญชีผู้ใช้';
    }
}
$pageTitle = 'เข้าสู่ระบบผู้ดูแลระบบ';
$extraHead = '<link rel="stylesheet" href="/assets/css/admin/ad_login.css">'; // ตอนนี้ยังไม่มีอะไรเพิ่มเฉพาะหน้านี้
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/admin/head_admin.php'; ?>
</head>

<body class="login-body">

    <div class="login-overlay"></div>

    <div class="login-container">
        <div class="login-card shadow-lg">

            <!-- โลโก้ + หัวข้อ -->
            <div class="login-header text-center">
                <div class="login-logo-wrap mb-3">
                    <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png"
                        alt="Logo"
                        class="login-logo img-fluid">
                </div>
                <h1 class="login-title mb-1">ระบบจองห้องพัก</h1>
                <p class="login-subtitle mb-0">เข้าสู่ระบบสำหรับผู้ดูแลระบบ</p>
            </div>

            <!-- แสดง Error -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-3">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- ฟอร์ม Login -->
            <form method="POST" class="login-form">

                <div class="mb-3">
                    <label for="username" class="form-label">ชื่อผู้ใช้</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-user text-muted"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            name="username"
                            required
                            autocomplete="username"
                            placeholder="กรอกชื่อผู้ใช้...">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">รหัสผ่าน</label>
                    <div class="input-group" id="passwordWrapper">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="กรอกรหัสผ่าน...">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100 mb-2">
                    เข้าสู่ระบบ
                </button>

                <p class="text-center text-muted mb-0">
                    <small>© <?= date('Y') ?> ระบบจองห้องพัก - สำหรับผู้ดูแลเท่านั้น</small>
                </p>
            </form>
        </div>
    </div>

    <?php include_once __DIR__ . '/../partials/admin/script_admin.php'; ?>
    <script>
        // toggle แสดง/ซ่อนรหัสผ่าน
        $(function() {
            $('.toggle-password').on('click', function() {
                const $input = $('#password');
                const type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);

                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });
        });
    </script>

</body>

</html>