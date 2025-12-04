<?php
require_once '../../utils/admin_guard.php';
require_once '../../db.php';
header('Content-Type: text/html; charset=utf-8');

/* -------------------------------------------------
    ฟังก์ชัน month ไทย
------------------------------------------------- */
function thaiMonth($m)
{
    $arr = [
        1 => "มกราคม",
        2 => "กุมภาพันธ์",
        3 => "มีนาคม",
        4 => "เมษายน",
        5 => "พฤษภาคม",
        6 => "มิถุนายน",
        7 => "กรกฎาคม",
        8 => "สิงหาคม",
        9 => "กันยายน",
        10 => "ตุลาคม",
        11 => "พฤศจิกายน",
        12 => "ธันวาคม"
    ];
    return $arr[(int)$m] ?? '';
}

/* -------------------------------------------------
    ฟังก์ชันวันที่ไทยแบบ 1 ก.ค. 68
------------------------------------------------- */
function formatDateThai($date)
{
    if (!$date) return "-";
    $d = new DateTime($date);
    $months = ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
    $m = (int)$d->format("n") - 1;
    return $d->format("j ") . $months[$m] . " " . ($d->format("Y") + 543);
}

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
    b.department,
    b.purpose,
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
    </style>
</head>

<body>
    <div class="container my-4">

        <!--================ Header =================-->
        <div class="text-center mb-4">
            <h4>รายการขอความอนุเคราะห์เข้าพักที่หอพักนิสิตแพทย์</h4>
            <h4>จากศูนย์แพทยศาสตร์ศึกษาชั้นคลินิก สถาบันการแพทย์ต่างๆ บุคคลภายนอก</h4>
            <h4>เพื่อมาศึกษารายวิชาฝึกปฏิบัติงาน ณ ภาควิชา คณะแพทยศาสตร์</h4>

            <h2 class="mt-3">
                เดือน <?= thaiMonth($month) ?> <?= $year + 543 ?>
            </h2>
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
        </form>

        <!--================ ตารางรายงาน =================-->
        <div class="report-container shadow-sm">
            <table class="table table-bordered text-center">
                <thead class="table-light">
                    <tr>
                        <th>เลขที่ใบจอง</th>
                        <th>ชื่อสถาบัน</th>
                        <th>วัตถุประสงค์</th>
                        <th>วันที่เข้าพัก</th>
                        <th>วันที่ย้ายออก</th>
                        <th>จำนวน<br>(ห้อง)</th>
                        <th>เลขที่ห้องพัก</th>
                        <th>รายชื่อประกอบ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <!-- เลขที่ใบจอง: 1 แถวต่อ 1 ใบจอง ไม่ซ้ำแล้ว -->
                            <td><?= "RM-" . str_pad($row['booking_id'], 4, "0", STR_PAD_LEFT) ?></td>

                            <!-- ชื่อสถาบัน -->
                            <td><?= htmlspecialchars($row['department'] ?: "-") ?></td>

                            <!-- วัตถุประสงค์ -->
                            <td><?= htmlspecialchars($row['purpose'] ?: "-") ?></td>

                            <!-- วันที่เข้าพัก/ย้ายออก -->
                            <td><?= formatDateThai($row['check_in_date']) ?></td>
                            <td><?= formatDateThai($row['check_out_date']) ?></td>

                            <!-- จำนวน(ห้อง) -->
                            <td><?= (int)($row['room_count'] ?? 0) ?></td>

                            <!-- เลขที่ห้องพัก: หลายเลข เช่น 101, 105 -->
                            <td><?= htmlspecialchars($row['room_list'] ?: "-") ?></td>

                            <!-- รายชื่อประกอบ -->
                            <td><?= htmlspecialchars($row['guest_list'] ?: "-") ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>