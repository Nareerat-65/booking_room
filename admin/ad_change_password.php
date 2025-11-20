<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ad_login.php');
    exit;
}

$pageTitle = 'เปลี่ยนรหัสผ่านผู้ดูแลระบบ';
$extraHead = ''; // ถ้ามี style เฉพาะก็เพิ่มทีหลังได้
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include 'partials/head_admin.php'; ?>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- NAVBAR -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link font-weight-bold">เปลี่ยนรหัสผ่านผู้ดูแลระบบ</span>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-flex align-items-center">
                    <span class="navbar-text mr-3">
                        <?= htmlspecialchars($_SESSION['admin_name']) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="ad_logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /NAVBAR -->

        <!-- SIDEBAR -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="ad_dashboard.php" class="brand-link d-flex align-items-center">
                <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" class="brand-image img-circle elevation-3" style="opacity:.9">
                <span class="brand-text font-weight-light ml-2">Admin Dashboard</span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image"><i class="fas fa-user-circle fa-2x text-white"></i></div>
                    <div class="info"><span class="d-block text-white">ผู้ดูแลระบบ</span></div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <li class="nav-item">
                            <a href="ad_dashboard.php" class="nav-link ">
                                <!-- แนะนำเปลี่ยนเป็น icon ที่มีจริงใน Font Awesome -->
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ad_requests.php" class="nav-link">
                                <i class="nav-icon fas fa-list"></i>
                                <p>รายการคำขอจองห้องพัก</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="ad_calendar.php" class="nav-link">
                                <i class="nav-icon fas fa-calendar"></i>
                                <p>ปฏิทินห้องพัก</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="ad_change_password.php" class="nav-link active">
                                <i class="nav-icon fas fa-key"></i>
                                <p>เปลี่ยนรหัสผ่าน</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="ad_logout.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>ออกจากระบบ</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        </aside>
        <!-- /SIDEBAR -->

        <!-- CONTENT -->
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <h2>🔑 เปลี่ยนรหัสผ่าน</h2>
                </div>
            </section>

            <section class="content">
                <div class="container">

                    <div class="card col-md-6 mx-auto">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">เปลี่ยนรหัสผ่าน</h5>
                        </div>

                        <div class="card-body">
                            <form id="changePassForm" method="POST" action="ad_change_password_process.php">

                                <div class="form-group">
                                    <label>รหัสผ่านเดิม:</label>
                                    <input type="password" name="old_password" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>รหัสผ่านใหม่:</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>ยืนยันรหัสผ่านใหม่:</label>
                                    <input type="password" id="confirm_password" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    บันทึกการเปลี่ยนแปลง
                                </button>

                            </form>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        <!-- FOOTER -->
        <footer class="main-footer text-sm">
            <div class="float-right d-none d-sm-inline">ระบบจองห้องพัก</div>
            <strong>&copy; <?= date('Y') ?> คณะ/หน่วยงานของคุณ</strong>
        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // ตรวจสอบรหัสผ่านใหม่ว่าตรงกันหรือไม่
        $("#changePassForm").on("submit", function(e) {
            let newPass = $("#new_password").val();
            let confirmPass = $("#confirm_password").val();

            if (newPass !== confirmPass) {
                e.preventDefault();
                alert("รหัสผ่านใหม่ทั้งสองช่องไม่ตรงกัน กรุณาลองอีกครั้ง");
            }
        });
    </script>

</body>

</html>