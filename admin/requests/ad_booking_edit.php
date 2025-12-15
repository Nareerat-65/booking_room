<?php
require_once __DIR__ . '/../../utils/admin_guard.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../utils/booking_helper.php';
require_once __DIR__ . '/../../services/bookingService.php';

$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($bookingId <= 0) {
    header('Location: ad_requests.php');
    exit;
}

$booking = getBookingById($conn, $bookingId);
if (!$booking) {
    header('Location: ad_requests.php');
    exit;
}

$status = $booking['status'] ?? 'pending';
if ($status !== 'approved') {
    header('Location: ad_requests.php?error=not_approved');
    exit;
}

$bookingCode = formatBookingCode((int)$booking['id'], $booking['check_in_date'] ?? null);

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $errors, $updated] = updateBooking($conn, $bookingId, $_POST);

    if ($ok) {
        $success = true;
        $booking = array_merge($booking, $updated);
    }
}

// helper h()
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$position      = $booking['position'] ?? '';
$studentYear   = $booking['student_year'] ?? '';
$positionOther = $booking['position_other'] ?? '';
$purpose       = $booking['purpose'] ?? '';
$checkInValue  = $booking['check_in_date'] ?? '';
$checkOutValue = $booking['check_out_date'] ?? '';
$womanCount    = (int)($booking['woman_count'] ?? 0);
$manCount      = (int)($booking['man_count'] ?? 0);

$activeMenu = 'requests';
$pageTitle  = 'แก้ไขข้อมูลการจอง';
$extraHead = '<link rel="stylesheet" href="/assets/css/admin/ad_booking_edit.css">';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../../partials/admin/head_admin.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include '../../partials/admin/nav_admin.php'; ?>
        <?php include '../../partials/admin/sidebar_admin.php'; ?>

        <main class="app-main">
            <div class="app-content-header py-3">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">แก้ไขข้อมูลการจอง</h2>
                        <div class="text-muted">
                            เลขที่ใบจอง: <strong><?= h($bookingCode) ?></strong>
                            <span class="ms-3">
                                สถานะ:
                                <span class="badge bg-success">อนุมัติแล้ว</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= h($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success">
                            บันทึกข้อมูลเรียบร้อยแล้ว
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['reallocated'])): ?>
                        <div class="alert alert-success">
                            จัดสรรห้องใหม่เรียบร้อยแล้ว
                        </div>
                    <?php elseif (isset($_GET['reallocate_error']) && $_GET['reallocate_error'] === 'full'): ?>
                        <div class="alert alert-danger">
                            ไม่สามารถจัดสรรห้องใหม่ได้ เนื่องจากไม่มีห้องว่างเพียงพอในช่วงวันที่กำหนด
                        </div>
                    <?php endif; ?>


                    <!-- ฟอร์มหลักสำหรับแก้ไขข้อมูล -->
                    <form method="post">
                        <div class="row g-3">

                            <!-- ข้อมูลผู้จอง -->
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">ข้อมูลผู้จอง</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">ชื่อผู้จอง</label>
                                            <input type="text" name="full_name" class="form-control"
                                                value="<?= h($booking['full_name'] ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">เบอร์โทร</label>
                                            <input type="text" name="phone" class="form-control"
                                                value="<?= h($booking['phone'] ?? '') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">LINE ID</label>
                                            <input type="text" name="line_id" class="form-control"
                                                value="<?= h($booking['line_id'] ?? '') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">อีเมล</label>
                                            <input type="email" name="email" class="form-control"
                                                value="<?= h($booking['email'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ตำแหน่ง / หน่วยงาน -->
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">ข้อมูลตำแหน่งและหน่วยงาน</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">สถานะ / ตำแหน่ง</label>
                                            <select name="position" id="position" class="form-select">
                                                <option value="">-- เลือก --</option>
                                                <option value="student" <?= $position === 'student'  ? 'selected' : '' ?>>นักศึกษา/นิสิตแพทย์</option>
                                                <option value="intern" <?= $position === 'intern'   ? 'selected' : '' ?>>แพทย์ใช้ทุน</option>
                                                <option value="resident" <?= $position === 'resident' ? 'selected' : '' ?>>แพทย์ประจำบ้าน</option>
                                                <option value="staff" <?= $position === 'staff'    ? 'selected' : '' ?>>เจ้าหน้าที่</option>
                                                <option value="other" <?= $position === 'other'    ? 'selected' : '' ?>>อื่น ๆ</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 position-student-group">
                                            <label class="form-label">ชั้นปี (กรณีเป็นนักศึกษา/นิสิตแพทย์)</label>
                                            <input type="number" name="student_year" min="1" max="10"
                                                class="form-control"
                                                value="<?= h($studentYear) ?>">
                                        </div>

                                        <div class="mb-3 position-other-group">
                                            <label class="form-label">ระบุรายละเอียดตำแหน่ง (กรณีอื่น ๆ)</label>
                                            <input type="text" name="position_other" class="form-control"
                                                value="<?= h($positionOther) ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">หน่วยงาน / สถาบันต้นสังกัด</label>
                                            <input type="text" name="department" class="form-control"
                                                value="<?= h($booking['department'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- วัตถุประสงค์ -->
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">วัตถุประสงค์การเข้าพัก</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label d-block">วัตถุประสงค์หลัก</label>
                                            <select name="purpose" id="purpose" class="form-select">
                                                <option value="">-- เลือก --</option>
                                                <option value="study" <?= $purpose === 'study'    ? 'selected' : '' ?>>ศึกษารายวิชา</option>
                                                <option value="elective" <?= $purpose === 'elective' ? 'selected' : '' ?>>Elective</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 purpose-study-group">
                                            <label class="form-label">ชื่อรายวิชา (กรณีศึกษารายวิชา)</label>
                                            <input type="text" name="study_course" class="form-control"
                                                value="<?= h($booking['study_course'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">ภาควิชาที่มาศึกษา</label>
                                            <input type="text" name="study_dept" class="form-control"
                                                value="<?= h($booking['study_dept'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 purpose-elective-group">
                                            <label class="form-label">ภาควิชา (Elective / หมุนเวียน)</label>
                                            <input type="text" name="elective_dept" class="form-control"
                                                value="<?= h($booking['elective_dept'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- วันที่และจำนวนคน -->
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">ช่วงวันที่เข้าพัก</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">วันที่เข้าพัก</label>
                                            <input type="date" name="check_in_date" class="form-control"
                                                value="<?= h($checkInValue) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">วันที่ย้ายออก</label>
                                            <input type="date" name="check_out_date" class="form-control"
                                                value="<?= h($checkOutValue) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">จำนวนผู้เข้าพัก</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">จำนวนผู้เข้าพัก (หญิง)</label>
                                            <input type="number" name="woman_count" class="form-control"
                                                min="0" value="<?= h($womanCount) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">จำนวนผู้เข้าพัก (ชาย)</label>
                                            <input type="number" name="man_count" class="form-control"
                                                min="0" value="<?= h($manCount) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <a href="ad_requests.php" class="btn btn-outline-secondary">
                                ย้อนกลับ
                            </a>

                            <button type="submit" class="btn btn-primary">
                                บันทึกการแก้ไข
                            </button>
                        </div>
                    </form>

                    <!-- ⭐ ฟอร์มแยกต่างหากสำหรับจัดสรรห้องใหม่ (ห้ามซ้อนใน form หลัก) -->
                    <div class="mt-3 d-flex justify-content-end">
                        <form method="post" action="ad_reallocate_rooms.php"
                            onsubmit="return confirm('ต้องการจัดสรรห้องใหม่หรือไม่? ห้องเดิมจะถูกลบและจัดใหม่ทั้งหมด');">
                            <input type="hidden" name="booking_id" value="<?= (int)$bookingId ?>">
                            <button type="submit" class="btn btn-warning">
                                จัดสรรห้องใหม่
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <?php include '../../partials/admin/script_admin.php'; ?>
    <script src="/assets/js/admin/ad_booking_edit.js"></script>
</body>

</html>