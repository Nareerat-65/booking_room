<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'ปฏิทินการจองห้องพัก';
$extraHead = '
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <style>
        #calendar {
            max-width: 1100px;
            margin: 20px auto 40px;
            background: #fff;
            padding: 20px;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        .fc-daygrid-day-number {
            color: #dc723aff !important;
            font-weight: 600;
        }

        .fc-col-header-cell-cushion {
            color: #dc723aff !important;
            font-size: 15px;
            font-weight: 600;
        }
    </style>
';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include 'partials/head_admin.php'; ?>
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
                    <span class="nav-link font-weight-bold">ปฏิทินการจองห้องพัก</span>
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
                <img src="../img/Medicine_Naresuan.png" alt="Logo" class="brand-image img-circle elevation-3"
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
                            <a href="ad_dashboard.php" class="nav-link">
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
                            <a href="ad_calendar.php" class="nav-link active">
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
                <div class="container-fluid text-center">
                    <h3 class="my-3">📅 ปฏิทินการใช้ห้องพัก</h3>
                    <p class="text-muted mb-2">
                        แสดงช่วงวันที่เข้าพักจริง + 3 วันสำหรับทำความสะอาด
                    </p>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div id="calendar"></div>
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

    <!-- Modal: รายละเอียดการเข้าพัก -->
    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">รายละเอียดผู้เข้าพัก</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
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

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                locale: 'th',
                firstDay: 0,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: 'ad_calendar_events.php',

                eventDidMount: function(info) {
                    if (info.event.extendedProps.tooltip) {
                        $(info.el).tooltip({
                            title: info.event.extendedProps.tooltip,
                            container: 'body',
                            placement: 'top',
                            trigger: 'hover',
                        });
                    }
                },

                eventClick: function(info) {
                    var ev = info.event;
                    var props = ev.extendedProps || {};

                    $('#eventRoom').text(props.room || '-');
                    $('#eventBooker').text(props.booker || '-');

                    var start = props.start_real || ev.startStr;
                    var end = props.end_real || (ev.end ? ev.end.toISOString().slice(0, 10) : '');
                    var dateText = start;
                    if (end) {
                        dateText += ' ถึง ' + end;
                    }
                    $('#eventDates').text(dateText);

                    $('#eventGuests').text(props.guests || 'ยังไม่มีรายชื่อผู้เข้าพัก');

                    $('#eventDetailModal').modal('show');
                }
            });

            calendar.render();
        });
    </script>

</body>

</html>