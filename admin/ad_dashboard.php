<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ad_login.php');
    exit;
}

require_once '../db.php';

//คำขอที่ยังไม่อนุมัติ
$sqlPending = "SELECT COUNT(*) AS c FROM bookings WHERE status = 'pending'";
$pending = (int) ($conn->query($sqlPending)->fetch_assoc()['c'] ?? 0);

// คำขอที่จะเข้าพักใน 7 วันข้างหน้า
$sqlUpcoming = "
    SELECT COUNT(*) AS c
    FROM bookings
    WHERE status = 'approved'
      AND check_in_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
";
$upcoming = (int) ($conn->query($sqlUpcoming)->fetch_assoc()['c'] ?? 0);

// จำนวนผู้เข้าพักตอนนี้
$sqlGuestsNow = "
    SELECT COALESCE(SUM(ra.woman_count + ra.man_count), 0) AS c
    FROM room_allocations ra
    JOIN bookings b ON ra.booking_id = b.id
    WHERE b.status = 'approved'
      AND CURDATE() BETWEEN ra.start_date AND ra.end_date
";
$guests_now = (int) ($conn->query($sqlGuestsNow)->fetch_assoc()['c'] ?? 0);

// จำนวนห้องว่างตอนนี้
$sqlTotalRooms = "SELECT COUNT(*) AS c FROM rooms";
$total_rooms = (int) ($conn->query($sqlTotalRooms)->fetch_assoc()['c'] ?? 0);
$sqlRoomsInUse = "
    SELECT COUNT(DISTINCT ra.room_id) AS c
    FROM room_allocations ra
    JOIN bookings b ON ra.booking_id = b.id
    WHERE b.status = 'approved'
      AND CURDATE() BETWEEN ra.start_date AND DATE_ADD(ra.end_date, INTERVAL 3 DAY)
";
$rooms_in_use = (int) ($conn->query($sqlRoomsInUse)->fetch_assoc()['c'] ?? 0);

$available_rooms = $total_rooms - $rooms_in_use;
if ($available_rooms < 0) $available_rooms = 0;

// ข้อมูลสถิติผู้เข้าพักแยกตามเพศ
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
$activeMenu = 'dashboard';
$pageTitle = 'แดชบอร์ดผู้ดูแล';
$extraHead = '<link rel="stylesheet" href="/assets/css/admin/ad_dashboard.css">';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/admin/head_admin.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include_once __DIR__ . '/../partials/admin/nav_admin.php'; ?>
        <?php include_once __DIR__ . '/../partials/admin/sidebar_admin.php'; ?>

        <!-- ===== Main Content ===== -->
        <main class="app-main">

            <div class="app-content-header py-3">
                <div class="container-fluid text-center">
                    <h2 class="my-3">👋 สวัสดีคุณ <?= htmlspecialchars($_SESSION['admin_name']) ?></h2>
                    <p class="text-muted mb-3">ภาพรวมสถานะห้องพักและคำขอ ณ วันนี้</p>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">

                    <div class="row dashboard-metrics g-3">
                        <!-- Pending -->
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

                        <!-- Upcoming -->
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

                        <!-- Guests now -->
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

                        <!-- Available rooms -->
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

                    <!-- Chart -->
                    <div class="row mt-4">
                        <div class="col-md-6 mx-auto">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title text-white mb-0">
                                        สถิติผู้เข้าพัก (ชาย / หญิง รายเดือน)
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="genderBarChart" style="height: 350px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <!-- ===== Footer ===== -->
        <footer class="app-footer text-sm">
            <div class="float-end d-none d-sm-inline">ระบบจองห้องพัก</div>
            <strong>&copy; <?= date('Y'); ?> คณะ/หน่วยงานของคุณ</strong> สงวนลิขสิทธิ์
        </footer>

    </div>

    <?php include_once __DIR__ . '/../partials/admin/script_admin.php'; ?>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        window.genderLabels = <?= json_encode($labels_gender); ?>;
        window.manData = <?= json_encode($data_man); ?>;
        window.womanData = <?= json_encode($data_woman); ?>;
    </script>
    <script src="/assets/js/admin/ad_dashboard.js"></script>
</body>

</html>