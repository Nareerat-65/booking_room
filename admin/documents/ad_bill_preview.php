<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once UTILS_PATH . '/admin_guard.php';
require_once CONFIG_PATH . '/db.php';
require_once UTILS_PATH . '/booking_helper.php';
require_once SERVICES_PATH . '/billService.php';
require_once SERVICES_PATH . '/bookingService.php';

$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if ($bookingId <= 0) {
    die('ไม่พบ booking_id');
}

$booking = getBookingById($conn, $bookingId);
if (!$booking) {
    die('ไม่พบข้อมูลการจอง');
}

// แนะนำ: ออกใบแจ้งเฉพาะ approved เพื่อกันข้อมูลยังไม่นิ่ง
if (($booking['status'] ?? 'pending') !== 'approved') {
    die('รายการนี้ยังไม่อนุมัติ ไม่สามารถออกใบแจ้งค่าใช้จ่ายได้');
}

/** ---------------- Helpers ---------------- */
function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function formatThaiDateShort(?string $ymd): string
{
    if (!$ymd) return '-';
    $dt = new DateTime($ymd);
    $months = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    $day   = (int)$dt->format('j');
    $month = (int)$dt->format('n');
    $yearBE = (int)$dt->format('Y') + 543;
    $yy = $yearBE % 100; // ให้เป็น 2 หลักแบบตัวอย่าง (เช่น 68)
    return $day . ' ' . ($months[$month] ?? '') . ' ' . $yy;
}

function fetchRoomNames(mysqli $conn, int $bookingId): array
{
    $sql = "
        SELECT DISTINCT r.room_name
        FROM room_allocations a
        JOIN rooms r ON r.id = a.room_id
        WHERE a.booking_id = ?
        ORDER BY r.room_name ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();

    $rooms = [];
    while ($row = $res->fetch_assoc()) {
        if (!empty($row['room_name'])) $rooms[] = $row['room_name'];
    }
    $stmt->close();

    return $rooms;
}

function fetchGuestNames(mysqli $conn, int $bookingId): array
{
    // 1) ถ้ามี room_guests ให้ใช้ก่อน (แปลว่าแอดมินจัดห้องแล้ว + ผูกกับ allocation)
    $sql1 = "SELECT guest_name FROM room_guests WHERE booking_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($sql1);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();
    $names = [];
    while ($row = $res->fetch_assoc()) {
        $n = trim((string)($row['guest_name'] ?? ''));
        if ($n !== '') $names[] = $n;
    }
    $stmt->close();

    if (!empty($names)) return $names;

    // 2) ถ้าไม่มี ให้ fallback ไป booking_guest_requests (รายชื่อที่ผู้ใช้กรอกตอนส่งฟอร์ม)
    $sql2 = "SELECT guest_name FROM booking_guest_requests WHERE booking_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($sql2);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();
    $names2 = [];
    while ($row = $res->fetch_assoc()) {
        $n = trim((string)($row['guest_name'] ?? ''));
        if ($n !== '') $names2[] = $n;
    }
    $stmt->close();

    return $names2;
}

/** ---------------- Data for bill ---------------- */
$data = getBillData($conn, $bookingId);
$bill = calculateBill($data['booking'], (int)$data['room_count'], (int)$data['people']);

$bookingCode = formatBookingCode((int)($booking['id'] ?? 0));

$checkInRaw  = $booking['check_in_date'] ?? null;
$checkOutRaw = $booking['check_out_date'] ?? null;

$thaiIn  = formatThaiDateShort($checkInRaw);
$thaiOut = formatThaiDateShort($checkOutRaw);

$fullName   = $booking['full_name'] ?? '-';
$department = $booking['department'] ?? '-';
$position   = $booking['position'] ?? '-';

$roomNames = fetchRoomNames($conn, $bookingId);
$roomLine  = (!empty($roomNames))
    ? (count($roomNames) === 1 ? $roomNames[0] : implode(', ', $roomNames))
    : '-';

$guestNames = fetchGuestNames($conn, $bookingId);

// สไตล์ข้อความรายการให้ใกล้ตัวอย่าง
$isOther = (($position ?? '') === 'other');

$rateText = $isOther
    ? 'ชำระค่าที่พัก คิดอัตรา คนละ 150 บาท/คน/วัน'
    : 'ชำระค่าไฟฟ้า คิดอัตรา คืนละ 35 บาท/ห้อง';

$periodText = "ระหว่างวันที่ {$thaiIn} - {$thaiOut}";
$sumDaysText = "รวม {$bill['days']} วัน";

$amount = (int)$bill['total'];

/** ---------------- Fixed contact / footer text (แก้ไขได้ตามหน่วยงาน) ---------------- */
$contactDormName = 'ชยุต'; // ผู้ดูแล/ผู้ออกใบแจ้ง (แก้ได้)
$contactDormTel  = '082-7946535';

$contactCoordName = 'คุณกนกวรรณ';
$contactCoordTel  = '094-0822403';

$contactFinanceName = 'คุณเหมียว';
$contactFinanceTel  = '055-967906';

// “ได้รับเงินจาก …” ในตัวอย่างเป็นชื่อหน่วยงาน
$payerLine = $department;

// วันที่ออกใบแจ้ง
$issueThai = formatThaiDateShort(date('Y-m-d'));

$pageTitle  = "ใบแจ้งรายละเอียดค่าใช้จ่าย #{$bookingCode}";
$extraHead = '<link rel="stylesheet" href="\assets\css\admin\ad_bill_preview.css">';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <?php include_once PARTIALS_PATH . '/admin/head_admin.php'; ?>
</head>

<body>
<div class="wrap">
    
    <div class="toolbar">
        <a class="btn" href="ad_doc_manage.php?booking_id=<?= (int)$bookingId ?>">กลับหน้าเอกสาร</a>
        <button class="btn primary" onclick="window.print()">พิมพ์ / บันทึกเป็น PDF</button>
    </div>

    <div class="paper">

        <!-- ===== NEW: Header block (ไม่ลบข้อมูลเดิม) ===== -->
        <div class="doc-header">
            <div class="brand">
                <div class="h1">ใบแจ้งรายละเอียดค่าใช้จ่าย</div>
                <div class="sub">เอกสารสำหรับออกใบเสร็จรับเงิน / ตรวจสอบข้อมูลค่าใช้จ่าย</div>
            </div>
            <div class="meta">
                เลขที่ใบจอง: <strong>#<?= h($bookingCode) ?></strong><br>
                วันที่ออกใบแจ้ง: <strong><?= h($issueThai) ?></strong>
            </div>
        </div>

        <div class="hr"></div>

        <!-- ===== Original content (จัดระยะใหม่ แต่ไม่ลบข้อมูล) ===== -->
        <div class="para center">
            เรียน เจ้าหน้าที่หน่วยการเงินรายได้ งานการเงิน<br>
            ขอความกรุณา ออกใบเสร็จรับเงินให้กับผู้เข้าพักตามรายละเอียด ดังนี้
        </div>

        <div class="para center lead highlight">
            ได้รับเงินจาก <?= h($payerLine) ?>
        </div>

        <div class="big-center">
            ห้อง <?= h($roomLine) ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>รายการ</th>
                    <th style="width:22%;">จำนวนเงิน</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="item">
                        - <?= h($rateText) ?><br>
                        <span class="subnote">(<?= h($periodText) ?>)</span><br>

                        <div style="margin-top: 8px; font-weight:900;">
                            <?= h($sumDaysText) ?> โดยมีรายชื่อผู้เข้าพัก ดังนี้
                        </div>

                        <div class="names-box">
                            <?php
                            // ถ้าไม่มีรายชื่อในตาราง ให้ fallback เป็นชื่อผู้จอง
                            $namesToShow = !empty($guestNames) ? $guestNames : [$fullName];

                            // แสดงสูงสุด 8 บรรทัดให้หน้ากระดาษไม่ล้น (ปรับได้)
                            $maxLines = 8;
                            $shown = 0;
                            foreach ($namesToShow as $nm) {
                                if ($shown >= $maxLines) break;
                                $shown++;
                                ?>
                                <div>
                                    <?= h($nm) ?> <span></span>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </td>
                    <td class="amount">
                        <?= number_format($amount) ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <div><strong>หมายเหตุ</strong> ผู้พักอาศัยย้ายออกจากหอพัก: วันที่ <?= h($thaiOut) ?></div>
            <div><strong><?= h($contactDormName) ?></strong> ผู้ดูแลหอพัก/ผู้ออกใบแจ้งฯ เบอร์ <?= h($contactDormTel) ?></div>

            <div class="hr"></div>

            <div>ผู้ประสานงาน เรื่องค่าใช้จ่ายฯ: <strong><?= h($contactCoordName) ?></strong> เบอร์ติดต่อ <?= h($contactCoordTel) ?></div>
            <div>งานการเงิน ติดต่อ: <strong><?= h($contactFinanceName) ?></strong> เจ้าหน้าที่การเงิน เบอร์ติดต่อ <?= h($contactFinanceTel) ?></div>

            <div class="note-box">
                ชำระเป็นเงินสด ก่อนเวลา 15.00 น. “ชำระในวันสุดท้าย ที่ย้ายของออกจากหอพัก”<br>
                กรณี วันที่ย้ายออกตรงกับวันเสาร์, วันอาทิตย์, วันหยุดราชการ ให้ชำระเงินก่อนวันหยุด<br>
                หาก ชื่อ - นามสกุล, วันที่ย้ายเข้า, วันที่ย้ายออก ผิด ไม่ถูกต้อง หรือ ต้องการให้ระบุที่อยู่ในใบเสร็จรับเงิน
                เพื่อนำไปเป็นหลักฐานเบิก-จ่ายค่าใช้จ่ายกับหน่วยงานต้นสังกัด ให้แจ้งกับเจ้าหน้าที่การเงินก่อนออกใบเสร็จฯ ทุกครั้ง
                เพื่อความถูกต้องของใบเสร็จฯ ที่ท่านจะได้รับ
            </div>

        </div>

    </div>
</div>
</body>
</html>

