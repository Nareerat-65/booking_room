<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once UTILS_PATH . '/admin_guard.php';

$activeMenu = 'change_password';
$pageTitle = 'เปลี่ยนรหัสผ่านผู้ดูแลระบบ';
$extraHead = '<link rel="stylesheet" href="/assets/css/admin/ad_change_password.css">';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include_once PARTIALS_PATH . '/admin/head_admin.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <!-- Navbar + Sidebar -->
        <?php include_once PARTIALS_PATH . '/admin/nav_admin.php'; ?>
        <?php include_once PARTIALS_PATH . '/admin/sidebar_admin.php'; ?>

        <!-- MAIN -->
        <main class="app-main">

            <div class="app-content-header py-3">
                <div class="container-fluid text-center">
                    <h2 class="my-3">🔑 เปลี่ยนรหัสผ่าน</h2>
                    <p class="text-muted mb-4">
                        กรุณากรอกรหัสผ่านเดิมและรหัสผ่านใหม่ที่ต้องการเปลี่ยนแปลง
                    </p>

                    <?php if (!empty($_GET['msg'])): ?>
                        <div class="alert alert-<?=
                                                $_GET['msg'] === 'ok' ? 'success' : ($_GET['msg'] === 'mismatch' ? 'warning' : ($_GET['msg'] === 'too_short' ? 'warning' : ($_GET['msg'] === 'old_wrong' ? 'danger' : 'danger')))
                                                ?> w-50 mx-auto">
                            <?=
                            $_GET['msg'] === 'ok' ? 'เปลี่ยนรหัสผ่านสำเร็จ' : ($_GET['msg'] === 'mismatch' ? 'รหัสผ่านใหม่ไม่ตรงกัน' : ($_GET['msg'] === 'too_short' ? 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร' : ($_GET['msg'] === 'old_wrong' ? 'รหัสผ่านเดิมไม่ถูกต้อง' :
                                            'ไม่สามารถเปลี่ยนรหัสผ่านได้')))
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="app-content">
                <div class="container d-flex justify-content-center">

                    <div class="changepass-card shadow-lg p-4">

                        <form method="POST" action="ad_change_password_process.php" class="changepass-form">

                            <!-- Old password -->
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่านเดิม</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" name="old_password" required class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- New password -->
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่านใหม่</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-key text-muted"></i>
                                    </span>
                                    <input type="password" name="new_password" required class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Confirm password -->
                            <div class="mb-4">
                                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-check-circle text-muted"></i>
                                    </span>
                                    <input type="password" name="confirm_password" required class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-changepass w-100 py-2">
                                บันทึกการเปลี่ยนแปลง
                            </button>

                        </form>

                    </div>

                </div>
            </div>

        </main>

        <?php include_once PARTIALS_PATH . '/admin/footer_admin.php'; ?>

    </div>

    <?php include_once PARTIALS_PATH . '/admin/script_admin.php'; ?>
    <script src="../../assets/js/admin/ad_change_password.js"></script>

</body>

</html>