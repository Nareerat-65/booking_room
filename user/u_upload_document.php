<?php
session_start();
require_once '../db.php';
require_once '../utils/booking_helper.php';
require_once '../services/documentService.php';

$bookingId = (int)($_GET['booking_id'] ?? 0);
if ($bookingId <= 0) {
    echo "ไม่พบคำขอจองห้องพักที่ต้องการ (booking_id ไม่ถูกต้อง)";
    exit;
}

$stmt = $conn->prepare("
    SELECT id, full_name, department, check_in_date, check_out_date, woman_count, man_count
    FROM bookings
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo "ไม่พบข้อมูลการจองห้องพักในระบบ";
    exit;
}
$bookingCode = formatBookingCode((int)$booking['id'] ?? null);

$docsResult = getDocumentsVisibleToUser($conn, $bookingId);
// helper แปลงขนาดไฟล์ให้อ่านง่าย
function formatSize($bytes)
{
    $bytes = (int)$bytes;
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}

$pageTitle = 'อัปโหลดเอกสารประกอบเพิ่มเติม - ระบบจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="/assets/css/user/u_upload_document.css">';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/user/head_user.php'; ?>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold py-2 d-flex align-items-center" href="#">
                <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" width="80" height="80" class="me-3">
                <span style="line-height:1; font-size:1.8rem;">
                    ระบบจองห้องพัก
                </span>
            </a>
        </div>
    </nav>
    <div class="container py-4">
        <h2 class=" text-center mb-4">อัปโหลดเอกสารประกอบเพิ่มเติม</h2>

        <!-- แสดงข้อมูล booking คร่าว ๆ -->
        <div class="card mb-4">
            <div class="card-body ">
                <h5 class="card-title mb-1">
                    <b>เลขที่ใบจอง #<?= htmlspecialchars($bookingCode) ?></b>
                </h5>
                <p class="mb-1"><b>ชื่อผู้จอง:</b> <?= htmlspecialchars($booking['full_name']) ?></p>
                <p class="mb-1"><b>ชื่อหน่วยงานต้นสังกัด :</b> <?= htmlspecialchars($booking['department'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <p class="mb-1"><b>ช่วงวันที่เข้าพัก:</b>
                    <?= htmlspecialchars(formatDate($booking['check_in_date'])) ?> ถึง
                    <?= htmlspecialchars(formatDate($booking['check_out_date'])) ?>
                </p>
                <p class="mb-0"><b>จำนวนทั้งหมด :</b>
                    หญิง <?= (int)$booking['woman_count'] ?> คน,
                    ชาย <?= (int)$booking['man_count'] ?> คน
                </p>
            </div>
        </div>

        <!-- แสดงข้อความผลอัปโหลด -->
        <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'uploaded'): ?>
            <div class="alert alert-success py-2">
                อัปโหลดเอกสารเรียบร้อยแล้ว
            </div>
        <?php elseif (!empty($_GET['error'])): ?>
            <div class="alert alert-danger py-2">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <!-- ฟอร์มอัปโหลดเอกสาร -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mt-1">อัปโหลดเอกสารประกอบเพิ่มเติม</h5>
            </div>
            <div class="card-body">
                <form action="u_upload_document_process.php" method="post" enctype="multipart/form-data">
                    <!-- ระบุ booking_id ที่เอกสารนี้เป็นของ -->
                    <input type="hidden" name="booking_id" value="<?= (int)$bookingId ?>">

                    <div class="mb-3">
                        <label for="documents" class="form-label">เลือกไฟล์เอกสาร</label>
                        <p class="text-muted small mb-3">
                            รองรับไฟล์: PDF, JPG, JPEG, PNG | ขนาดไม่เกิน 5 MB ต่อไฟล์
                        </p>
                        <input
                            class="form-control"
                            type="file"
                            id="documents"
                            name="documents[]"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png"
                            required>
                        <div class="form-text">
                            สามารถเลือกหลายไฟล์พร้อมกันได้
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        อัปโหลดเอกสาร
                    </button>
                </form>
            </div>
        </div>

        <!-- ตารางเอกสารทั้งหมด (ทั้ง user และ admin) ของ booking นี้ -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mt-1">เอกสารทั้งหมดของคำขอนี้</h5>
            </div>
            <div class="card-body">
                <?php if ($docsResult->num_rows === 0): ?>
                    <p class="text-muted mb-0">ยังไม่มีการอัปโหลดเอกสาร</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped doc-table align-middle">
                            <thead>
                                <tr>
                                    <th>ชื่อเอกสาร</th>
                                    <th>ผู้อัปโหลด</th>
                                    <th>ประเภทเอกสาร</th>
                                    <th>ขนาด</th>
                                    <th>วันที่อัปโหลด</th>
                                    <th class="text-center">เปิดเอกสาร</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($doc = $docsResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($doc['original_name']) ?></td>
                                        <td>
                                            <?php if ($doc['uploaded_by'] === 'admin'): ?>
                                                <span class="badge badge-admin bg-warning text-light">เจ้าหน้าที่</span>
                                            <?php else: ?>
                                                <span class="badge badge-user bg-success text-light">ผู้ใช้</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($doc['doc_type'] ?: '-') ?></td>
                                        <td><?= formatSize($doc['file_size']) ?></td>
                                        <td><?= htmlspecialchars($doc['uploaded_at']) ?></td>
                                        <td class="text-center">
                                            <?php
                                            // file_path เก็บแบบ relative เช่น uploads/documents/xxx.pdf
                                            $url = htmlspecialchars($doc['file_path'], ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <a href="../<?= $url ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                เปิด
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</body>

</html>