<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ad_login.php');
    exit;
}

require_once '../db.php';


/* 1) คำขอรออนุมัติ (booking status = pending) */
$sqlPending = "SELECT COUNT(*) AS c FROM bookings WHERE status = 'pending'";
$pending = (int) ($conn->query($sqlPending)->fetch_assoc()['c'] ?? 0);

/* 2) คำขอที่อนุมัติแล้ว และจะเข้าพักใน 7 วันข้างหน้า */
$sqlUpcoming = "
    SELECT COUNT(*) AS c
    FROM bookings
    WHERE status = 'approved'
      AND check_in_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
";
$upcoming = (int) ($conn->query($sqlUpcoming)->fetch_assoc()['c'] ?? 0);

/* 3) จำนวนผู้เข้าพักที่อยู่ตอนนี้ (ใช้ room_allocations + woman_count/man_count) */
$sqlGuestsNow = "
    SELECT COALESCE(SUM(ra.woman_count + ra.man_count), 0) AS c
    FROM room_allocations ra
    JOIN bookings b ON ra.booking_id = b.id
    WHERE b.status = 'approved'
      AND CURDATE() BETWEEN ra.start_date AND ra.end_date
";
$guests_now = (int) ($conn->query($sqlGuestsNow)->fetch_assoc()['c'] ?? 0);

/* 4) ห้องว่างตอนนี้ = ห้องทั้งหมด - ห้องที่ถูกใช้ (รวมช่วงพัก + ทำความสะอาด 3 วัน) */

/* 4.1 นับจำนวนห้องทั้งหมดจากตาราง rooms */
$sqlTotalRooms = "SELECT COUNT(*) AS c FROM rooms";
$total_rooms = (int) ($conn->query($sqlTotalRooms)->fetch_assoc()['c'] ?? 0);

/* 4.2 นับจำนวนห้องที่กำลังถูกใช้ (มี allocation ชนกับวันนี้) */
$sqlRoomsInUse = "
    SELECT COUNT(DISTINCT ra.room_id) AS c
    FROM room_allocations ra
    JOIN bookings b ON ra.booking_id = b.id
    WHERE b.status = 'approved'
      AND CURDATE() BETWEEN ra.start_date AND DATE_ADD(ra.end_date, INTERVAL 3 DAY)
      -- +3 วันหลัง end_date = ช่วงทำความสะอาด
";
$rooms_in_use = (int) ($conn->query($sqlRoomsInUse)->fetch_assoc()['c'] ?? 0);

$available_rooms = $total_rooms - $rooms_in_use;
if ($available_rooms < 0) $available_rooms = 0;

$pageTitle = 'แดชบอร์ดผู้ดูแล';
$extraHead = ''; // ตอนนี้ยังไม่มีอะไรเพิ่มเฉพาะหน้านี้

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/head_admin.php'; ?>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- TOP NAVBAR -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <!-- Left: ปุ่ม toggle sidebar + title -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link font-weight-bold">แดชบอร์ดผู้ดูแล</span>
                </li>
            </ul>

            <!-- Right: admin name + logout -->
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
        <!-- /TOP NAVBAR -->

        <!-- SIDEBAR -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="ad_dashboard.php" class="brand-link d-flex align-items-center">
                <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" class="brand-image img-circle elevation-3"
                    style="opacity:.9">
                <span class="brand-text font-weight-light ml-2">Admin Dashboard</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User info -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-white"></i>
                    </div>
                    <div class="info">
                        <span class="d-block text-white"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                    </div>
                </div>

                <!-- Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                        <li class="nav-item">
                            <a href="ad_dashboard.php" class="nav-link active">
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
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>ปฏิทินห้องพัก</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ad_change_password.php" class="nav-link">
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
            <!-- /Sidebar -->
        </aside>
        <!-- /SIDEBAR -->

        <!-- CONTENT WRAPPER -->
        <div class="content-wrapper">
            <!-- Header -->
            <section class="content-header">
                <div class="container-fluid text-center ">
                    <h2 class="my-3">👋 สวัสดีคุณ <?= htmlspecialchars($_SESSION['admin_name']) ?></h2>
                    <p>ภาพรวมห้องพักในวันนี้</p>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                    <!-- ===== กล่องตัวเลขสำคัญ ===== -->
                    <div class="row">
                        <!-- รออนุมัติ -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box" style="background:#F57B39; color:white;">
                                <div class="inner">
                                    <h3><?= $pending ?> รายการ</h3>
                                    <p>คำขอรออนุมัติ</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <a href="ad_requests.php" class="small-box-footer text-white">
                                    ดูรายการ <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- จะเข้าพักใน 7 วัน -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= $upcoming ?> รายการ</h3>
                                    <p>จะเข้าพักใน 7 วันข้างหน้า</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <a href="ad_requests.php" class="small-box-footer">
                                    ดูรายละเอียด <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- กำลังเข้าพัก -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success text-white">
                                <div class="inner">
                                    <h3><?= $guests_now ?> คน</h3>
                                    <p>กำลังเข้าพักตอนนี้</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <a href="ad_calendar.php" class="small-box-footer text-white">
                                    ไปหน้าปฏิทิน <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- ห้องว่างตอนนี้ -->
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary text-white">
                                <div class="inner">
                                    <h3><?= $available_rooms ?> ห้อง</h3>
                                    <p>ห้องว่างตอนนี้</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <a href="ad_calendar.php" class="small-box-footer text-white">
                                    ดูปฏิทินห้องพัก <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- ===== /กล่องตัวเลขสำคัญ ===== -->

                </div>
            </section>
        </div>
        <!-- /CONTENT WRAPPER -->

        <!-- FOOTER -->
        <footer class="main-footer text-sm">
            <div class="float-right d-none d-sm-inline">
                ระบบจองห้องพัก
            </div>
            <strong>&copy; <?= date('Y'); ?> คณะ/หน่วยงานของคุณ</strong> สงวนลิขสิทธิ์
        </footer>

    </div>
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>

</html>