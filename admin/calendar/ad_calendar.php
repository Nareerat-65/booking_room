<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location:  /admin/ad_login.php');
    exit;
}
$activeMenu = 'calendar';
$pageTitle = 'ปฏิทินการจองห้องพัก';
$extraHead = '
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin/ad_calendar.css">
';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../../partials/admin/head_admin.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include_once '../../partials/admin/nav_admin.php'; ?>
        <?php include_once '../../partials/admin/sidebar_admin.php'; ?>

        <main class="app-main">

            <div class="app-content-header py-3">
                <div class="container-fluid text-center">
                    <h2 class="my-3">📅 ปฏิทินการใช้ห้องพัก</h2>
                    <p class="text-muted mb-2">
                        แสดงช่วงเข้าพักจริง + 3 วันทำความสะอาด
                    </p>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div id="calendar"></div>
                </div>
            </div>

        </main>

        <?php include_once '../../partials/admin/footer_admin.php'; ?>

    </div>

    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">รายละเอียดผู้เข้าพัก</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <p><strong>ห้อง:</strong> <span id="eventRoom"></span></p>
                    <p><strong>ผู้จอง:</strong> <span id="eventBooker"></span></p>
                    <p><strong>ช่วงเข้าพัก:</strong> <span id="eventDates"></span></p>
                    <hr>
                    <p class="mb-1"><strong>รายชื่อผู้เข้าพัก</strong></p>
                    <pre id="eventGuests" class="mb-0" style="white-space: pre-wrap;"></pre>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../../partials/admin/script_admin.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
    <script src="../../assets/js/admin/ad_calendar.js"></script>


</body>

</html>