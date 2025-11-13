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

    <div id="calendar"></div>

    <!-- jQuery + Bootstrap 4 -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- FullCalendar JS -->

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // มุมมอง เดือน
                locale: 'th', // ภาษาไทย
                firstDay: 1, // เริ่มวันจันทร์
                selectable: true, // คลิกลากเลือกช่วงวันที่ได้
                editable: false, // ถ้าอยากลากย้าย event ได้ให้ true

                // โหลด event มาจาก PHP (เดี๋ยวทำไฟล์นี้ต่อ)
                events: 'calendar_events.php',

                // เวลาเลือกวันที่ (คลิก/ลาก) → ใช้ทำ "โน้ต" หรือเพิ่มรายการ
                select: function(info) {
                    const note = prompt(`เพิ่มโน้ตสำหรับวันที่ ${info.startStr} ถึง ${info.endStr} :`);
                    if (note) {
                        // เรียก AJAX ไปบันทึกโน้ต (เดี๋ยวเขียนไฟล์ PHP อีกตัว)
                        $.post('calendar_add_note.php', {
                            start: info.startStr,
                            end: info.endStr,
                            title: note
                        }, function(res) {
                            if (res === 'ok') {
                                calendar.refetchEvents(); // รีโหลด event ใหม่
                            } else {
                                alert('บันทึกไม่สำเร็จ');
                            }
                        });
                    }
                },

                // เวลา click event ที่มีอยู่แล้ว (เช่น การจอง หรือโน้ต)
                eventClick: function(info) {
                    alert(
                        'รายละเอียด:\n' +
                        'หัวข้อ: ' + info.event.title + '\n' +
                        'เริ่ม: ' + info.event.startStr + '\n' +
                        (info.event.endStr ? 'สิ้นสุด: ' + info.event.endStr : '')
                    );
                }
            });

            calendar.render();
        });
    </script>
</body>

</html>