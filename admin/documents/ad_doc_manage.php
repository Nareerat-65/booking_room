<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once UTILS_PATH . '/admin_guard.php';
require_once CONFIG_PATH . '/db.php';
require_once UTILS_PATH . '/booking_helper.php';
require_once SERVICES_PATH . '/documentService.php';
require_once SERVICES_PATH . '/bookingService.php';

/* --- FIX: ดึง booking_id ให้ถูกต้อง --- */
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if ($booking_id <= 0) {
    die("ไม่พบ booking_id");
}

/* --- โหลดข้อมูล booking --- */
$booking = getBookingById($conn, $booking_id);
if (!$booking) {
    die("ไม่พบข้อมูลการจอง");
}

/* --- โหลดเอกสารของ booking --- */
$docResult = getDocumentsByBookingId($conn, $booking_id);

$bookingCode = formatBookingCode($booking['id']);
$pageTitle  = "เลขที่ใบจอง #" . $bookingCode;
$activeMenu = "documents";
$extraHead = '<link rel="stylesheet" href="/assets/css/admin/ad_doc_manage.css">';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php include_once PARTIALS_PATH . '/admin/head_admin.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include_once PARTIALS_PATH . '/admin/nav_admin.php'; ?>
        <?php include_once PARTIALS_PATH . '/admin/sidebar_admin.php'; ?>

        <main class="app-main">
            <div class="app-content-header py-3">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-2">จัดการเอกสาร</h2>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-primary"
                            href="ad_bill_preview.php?booking_id=<?= (int)$booking['id'] ?>">
                            ออกใบแจ้งค่าใช้จ่าย
                        </a>
                        <a href="ad_doc_bookings.php" class="btn btn-secondary btn-sm">ย้อนกลับ</a>
                    </div>

                </div>
                <div class="container-fluid mt-2">
                    เลขที่ใบจอง: <strong><?= $bookingCode ?></strong>
                    <p>
                        ชื่อผู้จอง: <?= htmlspecialchars($booking['full_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br>
                        หน่วยงานต้นสังกัด: <?= htmlspecialchars($booking['department'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br>
                        เข้าพัก: <?= htmlspecialchars(formatDate($booking['check_in_date'])) ?>
                        ถึง <?= htmlspecialchars(formatDate($booking['check_out_date'])) ?><br>
                    </p>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">

                    <!-- ฟอร์มอัปโหลด -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">อัปโหลดเอกสารใหม่</h3>
                        </div>
                        <form action="ad_doc_upload.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="booking_id" value="<?= (int)$booking_id ?>">
                            <div class="card-body row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">ประเภทเอกสาร</label>
                                    <input type="text" name="doc_type" class="form-control" placeholder="เช่น หนังสืออนุมัติ">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">เอกสาร</label>
                                    <input type="file" name="file" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">แสดงให้ผู้ใช้เห็นหรือไม่</label>
                                    <select name="is_visible_to_user" class="form-select">
                                        <option value="1">แสดงให้ user เห็น</option>
                                        <option value="0">เฉพาะ admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn-save">บันทึกเอกสาร</button>
                            </div>
                        </form>
                    </div>

                    <!-- ตารางเอกสาร -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">รายการเอกสารทั้งหมด</h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-striped" id="docListTable">
                                <thead>
                                    <tr>
                                        <th>ประเภทเอกสาร</th>
                                        <th>ชื่อเอกสาร</th>
                                        <th>ผู้อัปโหลด</th>
                                        <th>ขนาด</th>
                                        <th>แสดงให้ user</th>
                                        <th>วันที่อัปโหลด</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($doc = $docResult->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($doc['doc_type'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($doc['original_name']) ?></td>
                                            <td><?= htmlspecialchars($doc['uploader_name'] ?? '-') ?></td>
                                            <td><?= number_format($doc['file_size'] / 1024, 2) ?> KB</td>
                                            <td><?= $doc['is_visible_to_user'] ? 'ใช่' : 'ไม่' ?></td>
                                            <td><?= htmlspecialchars(formatDate($doc['uploaded_at'])) ?></td>
                                            <td>
                                                <a href="ad_doc_download.php?id=<?= (int)$doc['id'] ?>"
                                                    class="btn btn-sm btn-info text-light">
                                                    ดู/ดาวน์โหลด
                                                </a>
                                                <a href="ad_doc_delete.php?id=<?= (int)$doc['id'] ?>&booking_id=<?= (int)$booking_id ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="deleteDocument(event, <?= (int)$doc['id'] ?>)">
                                                    ลบ
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <?php include_once PARTIALS_PATH . '/admin/footer_admin.php'; ?>
    </div>
    <?php include_once PARTIALS_PATH . '/admin/script_admin.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery && $.fn.DataTable) {
                $('#docListTable').DataTable();
            }
        });

        function deleteDocument(e, docID) {
            e.preventDefault();
            SA.confirm(
                'ยืนยันการลบเอกสาร',
                'คุณแน่ใจหรือไม่ว่าต้องการลบเอกสารนี้? การกระทำนี้ไม่สามารถย้อนกลับได้',
                'ลบ',
                'ยกเลิก',
                function() {
                    window.location.href = 'ad_doc_delete.php?id=' + docID + '&booking_id=<?= (int)$booking_id ?>';
                }
            );
        }
    </script>
</body>

</html>