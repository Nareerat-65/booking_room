<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ปฏิทินการจองห้องพัก</title>

    <!-- Bootstrap 4 + AdminLTE ที่ใช้ในโปรเจกต์อยู่แล้ว -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- FullCalendar (ใช้เวอร์ชัน 6.x แบบ CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Kanit&subset=thai,latin" rel="stylesheet" type="text/css" />

    <style>
        body {
            background: #fbf6f4ff;
            font-family: 'Kanit', sans-serif;
        }

        .navbar {
            font-size: 0.95rem;
            backdrop-filter: blur(12px);
            background-color: #F57B39;
        }

        .nav-link {
            transition: 0.3s;
            font-size: 1.1rem;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 0.5rem;
            padding-inline: 1rem;
        }

        .navbar-brand {
            font-size: 1.9rem;
        }

        #calendar {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark px-4">
        <a class="navbar-brand font-weight-bold py-2" href="#">แดชบอร์ดผู้ดูแล</a>

        <!-- ปุ่ม toggle สำหรับจอเล็ก -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- เมนูหลัก -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="ad_dashboard.php">รายการคำขอจองห้องพัก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="ad_calendar.php">ปฏิทินห้องพัก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">ติดต่อเรา</a>
                </li>
            </ul>

            <!-- ส่วนชื่อและปุ่มออก -->
            <div class="d-flex align-items-center text-white ml-3">
                <?= htmlspecialchars($_SESSION['admin_name']) ?>
                <a href="ad_logout.php" class="btn btn-outline-light btn-sm ml-3">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <div class="container pb-4 text-center">
        <h3 class="my-3">ปฏิทินการใช้ห้องพัก</h3>
        <p class="text-muted mb-2">
            แสดงช่วงวันที่เข้าพักจริง + 3 วันสำหรับทำความสะอาด
        </p>
        <div id="calendar"></div>
    </div>

    <!-- jQuery + Bootstrap 4 -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- FullCalendar JS -->

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js'></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                locale: 'th',
                firstDay: 0, // อาทิตย์
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: 'ad_calendar_events.php',
                eventDidMount: function(info) {
                    // เอา tooltip ขึ้นเวลา hover
                    if (info.event.extendedProps.tooltip) {
                        $(info.el).tooltip({
                            title: info.event.extendedProps.tooltip,
                            container: 'body',
                            placement: 'top',
                            trigger: 'hover',
                        });
                    }
                }
            });

            calendar.render();
        });
    </script>

</body>

</html>