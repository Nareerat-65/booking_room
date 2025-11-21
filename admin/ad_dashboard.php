<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ad_login.php');
    exit;
}

require_once '../db.php';

$sqlPending = "SELECT COUNT(*) AS c FROM bookings WHERE status = 'pending'";
$pending = (int) ($conn->query($sqlPending)->fetch_assoc()['c'] ?? 0);

$sqlUpcoming = "
    SELECT COUNT(*) AS c
    FROM bookings
    WHERE status = 'approved'
      AND check_in_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
";
$upcoming = (int) ($conn->query($sqlUpcoming)->fetch_assoc()['c'] ?? 0);

$sqlGuestsNow = "
    SELECT COALESCE(SUM(ra.woman_count + ra.man_count), 0) AS c
    FROM room_allocations ra
    JOIN bookings b ON ra.booking_id = b.id
    WHERE b.status = 'approved'
      AND CURDATE() BETWEEN ra.start_date AND ra.end_date
";
$guests_now = (int) ($conn->query($sqlGuestsNow)->fetch_assoc()['c'] ?? 0);

$sqlTotalRooms = "SELECT COUNT(*) AS c FROM rooms";
$total_rooms = (int) ($conn->query($sqlTotalRooms)->fetch_assoc()['c'] ?? 0);

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

$sqlGenderChart = "
    SELECT 
        DATE_FORMAT(ra.start_date, '%Y-%m') AS m,
        SUM(ra.man_count) AS total_man,
        SUM(ra.woman_count) AS total_woman
    FROM room_allocations ra
    JOIN bookings b ON ra.booking_id = b.id
    WHERE b.status = 'approved'
    GROUP BY m
    ORDER BY m ASC
";

$labels_gender = [];
$data_man = [];
$data_woman = [];

$resultGender = $conn->query($sqlGenderChart);
if ($resultGender && $resultGender->num_rows > 0) {
    while ($row = $resultGender->fetch_assoc()) {
        $labels_gender[] = $row['m'];
        $data_man[]      = (int)$row['total_man'];
        $data_woman[]    = (int)$row['total_woman'];
    }
}


$pageTitle = 'แดชบอร์ดผู้ดูแล';
$extraHead = '<link rel="stylesheet" href="/assets/css/admin/ad_dashboard.css">';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/head_admin.php'; ?>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-dark">
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

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="ad_dashboard.php" class="brand-link d-flex align-items-center">
                <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" class="brand-image img-circle elevation-3"
                    style="opacity:.9">
                <span class="brand-text font-weight-light ml-2">Admin Dashboard</span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-white"></i>
                    </div>
                    <div class="info">
                        <span class="d-block text-white"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                        <li class="nav-item">
                            <a href="ad_dashboard.php" class="nav-link active">
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
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid text-center">
                    <h2 class="my-3">👋 สวัสดีคุณ <?= htmlspecialchars($_SESSION['admin_name']) ?></h2>
                    <p class="text-muted mb-1">ภาพรวมสถานะห้องพักและคำขอ ณ วันนี้</p>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row dashboard-metrics">
                        <div class="col-lg-3 col-6">
                            <div class="small-box box-pending">
                                <div class="inner">
                                    <h3><?= $pending ?> รายการ</h3>
                                    <p>คำขอรออนุมัติ</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <a href="ad_requests.php" class="small-box-footer">
                                    ดูรายการ <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box box-upcoming">
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

                        <div class="col-lg-3 col-6">
                            <div class="small-box box-guests">
                                <div class="inner">
                                    <h3><?= $guests_now ?> คน</h3>
                                    <p>กำลังเข้าพักตอนนี้</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <a href="ad_calendar.php" class="small-box-footer">
                                    ไปหน้าปฏิทิน <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box box-available">
                                <div class="inner">
                                    <h3><?= $available_rooms ?> ห้อง</h3>
                                    <p>ห้องว่างตอนนี้</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <a href="ad_calendar.php" class="small-box-footer">
                                    ดูปฏิทินห้องพัก <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4 align-items-center">
                        <div class="col-md-6 ">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">สถิติผู้เข้าพัก (ชาย / หญิง รายเดือน)</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="genderLineChart" style="height: 350px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        window.genderLabels = <?= json_encode($labels_gender); ?>;
        window.manData = <?= json_encode($data_man); ?>;
        window.womanData = <?= json_encode($data_woman); ?>;
    </script>
    <script src="/assets/js/admin/ad_dashboard.js"></script>
</body>

</html>