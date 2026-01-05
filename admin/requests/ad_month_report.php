<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once UTILS_PATH . '/admin_guard.php';
require_once UTILS_PATH . '/booking_helper.php';
require_once CONFIG_PATH . '/db.php';
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
    b.full_name,
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

$pageTitle = 'รายการจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="/assets/css/admin/ad_month_report.css">';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php include_once PARTIALS_PATH . '/admin/head_admin.php'; ?>
</head>

<body>
    <div class="page-wrapper">

        <!--================ Header =================-->
        <div class="report-header mb-3">
            <div class="text-center">
                <h5>รายการขอความอนุเคราะห์เข้าพักที่หอพักนิสิตแพทย์</h5>
                <h5>จากศูนย์แพทยศาสตร์ศึกษาชั้นคลินิก สถาบันการแพทย์ต่างๆ บุคคลภายนอก</h5>
                <h5>เพื่อมาศึกษารายวิชาฝึกปฏิบัติงาน ณ ภาควิชา คณะแพทยศาสตร์</h5>

                <h3 class="mt-3">
                    เดือน <?= thaiMonth($month) ?> <?= $year + 543 ?>
                </h3>
            </div>
        </div>

        <!--================ Form เลือกเดือน =================-->
        <div class="filter-bar mb-4 d-flex flex-wrap align-items-center gap-2 no-print">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-0">
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
                <button class="btn-main" type="submit">
                    <i class="fas fa-search"></i> แสดงรายงาน
                </button>
                <button type="button" class="btn btn-outline-secondary btn-print" onclick="window.print()">
                    <i class="fas fa-print" aria-hidden="true"></i> พิมพ์รายงาน
                </button>
            </form>

            <div class="ms-auto">
                <a class="btn btn-outline-secondary btn-back" href="ad_requests.php">กลับหน้าเอกสาร</a>
            </div>
        </div>

        <!--================ ตารางรายงาน =================-->
        <div class="report-container">
            <table class="table table-bordered text-center mb-0">
                <thead>
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

                            <!-- ภาควิชา -->
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