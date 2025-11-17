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

        .fc-daygrid-day-number {
            color: #dc723aff !important;
            /* สีตัวเลขวันที่ */
            font-weight: 600;
        }

        .fc-col-header-cell-cushion {
            color: #dc723aff !important;
            /* สีของชื่อวัน */
            font-size: 15px;
            font-weight: 600;
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
                firstDay: 0,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: 'ad_calendar_events.php',

                eventDidMount: function(info) {
                    // tooltip เวลา hover
                    if (info.event.extendedProps.tooltip) {
                        $(info.el).tooltip({
                            title: info.event.extendedProps.tooltip,
                            container: 'body',
                            placement: 'top',
                            trigger: 'hover',
                        });
                    }
                },

                // 👇 เพิ่มส่วนนี้
                eventClick: function(info) {
                    var ev = info.event;
                    var props = ev.extendedProps || {};

                    // ห้อง / ผู้จอง
                    $('#eventRoom').text(props.room || '-');
                    $('#eventBooker').text(props.booker || '-');

                    // ช่วงวันที่เข้าพัก (ใช้ start_real / end_real จาก PHP)
                    var start = props.start_real || ev.startStr;
                    var end = props.end_real || (ev.end ? ev.end.toISOString().slice(0, 10) : '');
                    var dateText = start;
                    if (end) {
                        dateText += ' ถึง ' + end;
                    }
                    $('#eventDates').text(dateText);

                    // รายชื่อผู้เข้าพัก
                    $('#eventGuests').text(props.guests || 'ยังไม่มีรายชื่อผู้เข้าพัก');

                    // เปิด modal
                    $('#eventDetailModal').modal('show');
                }
            });


            calendar.render();
        });
    </script>

</body>

</html>