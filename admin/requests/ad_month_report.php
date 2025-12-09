<?php
require_once '../../utils/admin_guard.php';
require_once '../../utils/booking_helper.php';
require_once '../../db.php';
header('Content-Type: text/html; charset=utf-8');

/* -------------------------------------------------
    รับค่าที่ผู้ใช้เลือก
------------------------------------------------- */
$month = $_GET['month'] ?? date("m");
$year  = $_GET['year'] ?? date("Y");

/* -------------------------------------------------
    SQL ดึงข้อมูลรายงาน
------------------------------------------------- */

$sql = "
SELECT 
    b.id AS booking_id,
    b.	full_name,
    b.department,
    b.study_dept,
    b.elective_dept,
    b.purpose,
    b.study_course,
    b.check_in_date,
    b.check_out_date,

    COUNT(DISTINCT ra.room_id) AS room_count,
    GROUP_CONCAT(DISTINCT r.room_name ORDER BY r.room_name SEPARATOR ', ') AS room_list,

    (
        SELECT GROUP_CONCAT(g.guest_name SEPARATOR ', ')
        FROM room_guests g
        WHERE g.booking_id = b.id
    ) AS guest_list

FROM bookings b
LEFT JOIN room_allocations ra ON b.id = ra.booking_id
LEFT JOIN rooms r            ON ra.room_id = r.id
WHERE b.status = 'approved'       
  AND MONTH(b.check_in_date) = ? 
  AND YEAR(b.check_in_date) = ?
GROUP BY 
    b.id,
    b.department,
    b.purpose,
    b.check_in_date,
    b.check_out_date
ORDER BY b.id ASC
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานประจำเดือน</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #fafafa;
            font-family: "TH Sarabun New", sans-serif;
        }

        .report-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
        }

        table th,
        table td {
            vertical-align: middle !important;
            font-size: 18px;
        }

        h2,
        h4 {
            font-weight: bold;
        }

        /* เดิมเราเขียนไว้ประมาณนี้ */
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        /* แก้เป็น landscape แทน */
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        @media print {

            html,
            body {
                margin: 0;
                padding: 0;
                width: 100%;
            }

            .container {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .report-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
            }

            table.table {
                width: 100%;
                table-layout: fixed;
                /* ให้แบ่งคอลัมน์เต็มหน้า */
            }

            table th,
            table td {
                font-size: 14px;
                /* ลดไซส์ลงนิดนึง */
                padding: 4px 6px;
                white-space: normal;
                word-break: break-word;
                /* ให้ตัดคำลงบรรทัดใหม่ได้ */
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="container my-4">

        <!--================ Header =================-->
        <div class="text-center mb-4">
            <h5>รายการขอความอนุเคราะห์เข้าพักที่หอพักนิสิตแพทย์</h5>
            <h5>จากศูนย์แพทยศาสตร์ศึกษาชั้นคลินิก สถาบันการแพทย์ต่างๆ บุคคลภายนอก</h5>
            <h5>เพื่อมาศึกษารายวิชาฝึกปฏิบัติงาน ณ ภาควิชา คณะแพทยศาสตร์</h5>

            <h3 class="mt-3">
                เดือน <?= thaiMonth($month) ?> <?= $year + 543 ?>
            </h3>
        </div>

        <!--================ Form เลือกเดือน =================-->
        <form method="GET" class="mb-4 d-flex gap-2 justify-content-center">
            <select name="month" class="form-select w-auto">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $sel = ($m == $month) ? "selected" : "";
                    echo "<option value='$m' $sel>" . thaiMonth($m) . "</option>";
                }
                ?>
            </select>

            <select name="year" class="form-select w-auto">
                <?php
                for ($y = date("Y") - 3; $y <= date("Y") + 1; $y++) {
                    $sel = ($y == $year) ? "selected" : "";
                    echo "<option value='$y' $sel>" . $y . "</option>";
                }
                ?>
            </select>

            <button class="btn btn-primary">แสดงรายงาน</button>

            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print" aria-hidden="true"></i>
            </button>
        </form>

        <!--================ ตารางรายงาน =================-->
        <div class="report-container shadow-sm">
            <table class="table table-bordered text-center">
                <thead class="table-light">
                    <tr>
                        <th>เลขที่ใบจอง</th>
                        <th>ชื่อสถาบัน</th>
                        <th>วัตถุประสงค์</th>
                        <th>ภาควิชา</th>
                        <th>วันที่เข้าพัก</th>
                        <th>วันที่ย้ายออก</th>
                        <th>จำนวน<br>(ห้อง)</th>
                        <th>เลขที่ห้องพัก</th>
                        <th>ชื่อผู้ประสานงาน</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <!-- เลขที่ใบจอง: 1 แถวต่อ 1 ใบจอง ไม่ซ้ำแล้ว -->
                            <td><?= formatBookingCode($row['booking_id']) ?></td>

                            <!-- ชื่อสถาบัน -->
                            <td><?= htmlspecialchars($row['department'] ?: "-") ?></td>

                            <!-- วัตถุประสงค์ -->
                            <td><?= htmlspecialchars(formatPurpose($row)) ?></td>

                            <td><?= htmlspecialchars($row['study_dept'] ?: ($row['elective_dept'] ?: "-")) ?></td>

                            <!-- วันที่เข้าพัก/ย้ายออก -->
                            <td><?= formatDate($row['check_in_date']) ?></td>
                            <td><?= formatDate($row['check_out_date']) ?></td>

                            <!-- จำนวน(ห้อง) -->
                            <td><?= (int)($row['room_count'] ?? 0) ?></td>

                            <!-- เลขที่ห้องพัก: หลายเลข เช่น 101, 105 -->
                            <td><?= htmlspecialchars($row['room_list'] ?: "-") ?></td>

                            <!-- รายชื่อประกอบ -->
                            <td><?= htmlspecialchars($row['full_name'] ?: "-") ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>