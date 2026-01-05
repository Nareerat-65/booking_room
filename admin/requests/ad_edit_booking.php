<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once UTILS_PATH . '/admin_guard.php';
require_once CONFIG_PATH . '/db.php';
require_once UTILS_PATH . '/booking_helper.php';
require_once SERVICES_PATH . '/bookingService.php';

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
function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$position      = $booking['position'] ?? '';
$studentYear   = $booking['student_year'] ?? '';
$positionOther = $booking['position_other'] ?? '';
$purpose       = $booking['purpose'] ?? '';
$study_dept    = $booking['study_dept'] ?? '';
$elective_dept = $booking['elective_dept'] ?? '';
$checkInValue  = $booking['check_in_date'] ?? '';
$checkOutValue = $booking['check_out_date'] ?? '';
$womanCount    = (int)($booking['woman_count'] ?? 0);
$manCount      = (int)($booking['man_count'] ?? 0);
$guestRows = getBookingGuestRequests($conn, $bookingId);

$activeMenu = 'requests';
$pageTitle  = 'แก้ไขข้อมูลการจอง';
$extraHead = '<link rel="stylesheet" href="/assets/css/admin/ad_edit_booking.css">';
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
                        <h2 class="mb-2">แก้ไขข้อมูลการจอง</h2>
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
                                            <label class="form-label">ตำแหน่ง</label>
                                            <select name="position" id="position" class="form-select">
                                                <option value="">เลือกตำแหน่ง</option>
                                                <option value="student" <?= $position === 'student'  ? 'selected' : '' ?>>นักศึกษา/นิสิตแพทย์</option>
                                                <option value="intern" <?= $position === 'intern'   ? 'selected' : '' ?>>แพทย์ใช้ทุน</option>
                                                <option value="resident" <?= $position === 'resident' ? 'selected' : '' ?>>แพทย์ประจำบ้าน</option>
                                                <option value="staff" <?= $position === 'staff'    ? 'selected' : '' ?>>เจ้าหน้าที่</option>
                                                <option value="other" <?= $position === 'other'    ? 'selected' : '' ?>>อื่น ๆ</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 position-student-group">
                                            <label class="form-label">ชั้นปี (กรณีเป็นนักศึกษา/นิสิตแพทย์)</label>
                                            <select name="student_year" id="student_year" class="form-select" style="max-width: 120px;">
                                                <option value="">เลือกชั้นปี</option>
                                                <option value="1" <?= $studentYear == '1' ? 'selected' : '' ?>>1</option>
                                                <option value="2" <?= $studentYear == '2' ? 'selected' : '' ?>>2</option>
                                                <option value="3" <?= $studentYear == '3' ? 'selected' : '' ?>>3</option>
                                                <option value="4" <?= $studentYear == '4' ? 'selected' : '' ?>>4</option>
                                                <option value="5" <?= $studentYear == '5' ? 'selected' : '' ?>>5</option>
                                                <option value="6" <?= $studentYear == '6' ? 'selected' : '' ?>>6</option>
                                            </select>
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
                                                <option value="">เลือกวัตถุประสงค์</option>
                                                <option value="study" <?= $purpose === 'study'    ? 'selected' : '' ?>>ศึกษารายวิชา</option>
                                                <option value="elective" <?= $purpose === 'elective' ? 'selected' : '' ?>>Elective</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 purpose-study-group">
                                            <label class="form-label">ชื่อรายวิชา (กรณีศึกษารายวิชา)</label>
                                            <input type="text" name="study_course" class="form-control"
                                                value="<?= h($booking['study_course'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 purpose-study-group">
                                            <label class="form-label">ภาควิชาที่มาศึกษา</label>
                                            <select name="study_dept" id="study_dept" class="form-select">
                                                <option value="">เลือกภาควิชา</option>
                                                <option value="กุมารเวชศาสตร์" <?= $study_dept === 'กุมารเวชศาสตร์' ? 'selected' : '' ?>>กุมารเวชศาสตร์</option>
                                                <option value="จักษุวิทยา" <?= $study_dept === 'จักษุวิทยา' ? 'selected' : '' ?>>จักษุวิทยา</option>
                                                <option value="จิตเวชศาสตร์" <?= $study_dept === 'จิตเวชศาสตร์' ? 'selected' : '' ?>>จิตเวชศาสตร์</option>
                                                <option value="นิติเวชศาสตร์" <?= $study_dept === 'นิติเวชศาสตร์' ? 'selected' : '' ?>>นิติเวชศาสตร์</option>
                                                <option value="พยาธิวิทยา" <?= $study_dept === 'พยาธิวิทยา' ? 'selected' : '' ?>>พยาธิวิทยา</option>
                                                <option value="รังสีวิทยา" <?= $study_dept === 'รังสีวิทยา' ? 'selected' : '' ?>>รังสีวิทยา</option>
                                                <option value="วิสัญญีวิทยา" <?= $study_dept === 'วิสัญญีวิทยา' ? 'selected' : '' ?>>วิสัญญีวิทยา</option>
                                                <option value="ศัลยศาสตร์" <?= $study_dept === 'ศัลยศาสตร์' ? 'selected' : '' ?>>ศัลยศาสตร์</option>
                                                <option value="สูติศาสตร์-นรีเวชวิทยา" <?= $study_dept === 'สูติศาสตร์-นรีเวชวิทยา' ? 'selected' : '' ?>>สูติศาสตร์-นรีเวชวิทยา</option>
                                                <option value="ออร์โธปิดิกส์" <?= $study_dept === 'ออร์โธปิดิกส์' ? 'selected' : '' ?>>ออร์โธปิดิกส์</option>
                                                <option value="อายุรศาสตร์" <?= $study_dept === 'อายุรศาสตร์' ? 'selected' : '' ?>>อายุรศาสตร์</option>
                                                <option value="เวชศาสตร์ครอบครัว" <?= $study_dept === 'เวชศาสตร์ครอบครัว' ? 'selected' : '' ?>>เวชศาสตร์ครอบครัว</option>
                                                <option value="เวชศาสตร์ชุมชน" <?= $study_dept === 'เวชศาสตร์ชุมชน' ? 'selected' : '' ?>>เวชศาสตร์ชุมชน</option>
                                                <option value="เวชศาสตร์ฟื้นฟู" <?= $study_dept === 'เวชศาสตร์ฟื้นฟู' ? 'selected' : '' ?>>เวชศาสตร์ฟื้นฟู</option>
                                                <option value="โสต ศอ นาสิกวิทยา" <?= $study_dept === 'โสต ศอ นาสิกวิทยา' ? 'selected' : '' ?>>โสต ศอ นาสิกวิทยา</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 purpose-elective-group">
                                            <label class="form-label">ภาควิชา (Elective)</label>
                                            <select name="elective_dept" id="elective_dept" class="form-select">
                                                <option value="">เลือกภาควิชา</option>
                                                <option value="กุมารเวชศาสตร์" <?= $elective_dept === 'กุมารเวชศาสตร์' ? 'selected' : '' ?>>กุมารเวชศาสตร์</option>
                                                <option value="จักษุวิทยา" <?= $elective_dept === 'จักษุวิทยา' ? 'selected' : '' ?>>จักษุวิทยา</option>
                                                <option value="จิตเวชศาสตร์" <?= $elective_dept === 'จิตเวชศาสตร์' ? 'selected' : '' ?>>จิตเวชศาสตร์</option>
                                                <option value="นิติเวชศาสตร์" <?= $elective_dept === 'นิติเวชศาสตร์' ? 'selected' : '' ?>>นิติเวชศาสตร์</option>
                                                <option value="พยาธิวิทยา" <?= $elective_dept === 'พยาธิวิทยา' ? 'selected' : '' ?>>พยาธิวิทยา</option>
                                                <option value="รังสีวิทยา" <?= $elective_dept === 'รังสีวิทยา' ? 'selected' : '' ?>>รังสีวิทยา</option>
                                                <option value="วิสัญญีวิทยา" <?= $elective_dept === 'วิสัญญีวิทยา' ? 'selected' : '' ?>>วิสัญญีวิทยา</option>
                                                <option value="ศัลยศาสตร์" <?= $elective_dept === 'ศัลยศาสตร์' ? 'selected' : '' ?>>ศัลยศาสตร์</option>
                                                <option value="สูติศาสตร์-นรีเวชวิทยา" <?= $elective_dept === 'สูติศาสตร์-นรีเวชวิทยา' ? 'selected' : '' ?>>สูติศาสตร์-นรีเวชวิทยา</option>
                                                <option value="ออร์โธปิดิกส์" <?= $elective_dept === 'ออร์โธปิดิกส์' ? 'selected' : '' ?>>ออร์โธปิดิกส์</option>
                                                <option value="อายุรศาสตร์" <?= $elective_dept === 'อายุรศาสตร์' ? 'selected' : '' ?>>อายุรศาสตร์</option>
                                                <option value="เวชศาสตร์ครอบครัว" <?= $elective_dept === 'เวชศาสตร์ครอบครัว' ? 'selected' : '' ?>>เวชศาสตร์ครอบครัว</option>
                                                <option value="เวชศาสตร์ชุมชน" <?= $elective_dept === 'เวชศาสตร์ชุมชน' ? 'selected' : '' ?>>เวชศาสตร์ชุมชน</option>
                                                <option value="เวชศาสตร์ฟื้นฟู" <?= $elective_dept === 'เวชศาสตร์ฟื้นฟู' ? 'selected' : '' ?>>เวชศาสตร์ฟื้นฟู</option>
                                                <option value="โสต ศอ นาสิกวิทยา" <?= $elective_dept === 'โสต ศอ นาสิกวิทยา' ? 'selected' : '' ?>>โสต ศอ นาสิกวิทยา</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- วันที่และจำนวนคน -->
                            <div class="col-lg-6 mb-3">
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

                            <div class="col-lg-6 mb-3">
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
                        <!-- รายชื่อผู้เข้าพัก -->
                        <div class="col-lg-12">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex align-items-center">
                                    <h5 class="card-title mb-0">รายชื่อผู้เข้าพัก</h5>

                                    <button type="button" class="btn btn-sm btn-light ms-auto" id="btnAddGuest">
                                        <i class="fas fa-user-plus me-1"></i> เพิ่มรายชื่อ
                                    </button>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm align-middle mb-0" id="guestTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 56px;" class="text-center">#</th>
                                                    <th style="width: 40%;">ชื่อ-สกุล</th>
                                                    <th style="width: 20%;">เพศ</th>
                                                    <th style="width: 30%;">เบอร์โทร</th>
                                                    <th style="width: 90px;" class="text-end">จัดการ</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if (!empty($guestRows)): ?>
                                                    <?php $idx = 1;
                                                    foreach ($guestRows as $g): ?>
                                                        <tr class="guest-row">
                                                            <td class="text-center text-muted fw-semibold guest-no"><?= $idx++ ?></td>
                                                            <td>
                                                                <input type="text"
                                                                    name="guest_name[]"
                                                                    class="form-control form-control-sm"
                                                                    value="<?= h($g['guest_name'] ?? '') ?>"
                                                                    placeholder="ชื่อผู้เข้าพัก">
                                                            </td>
                                                            <td>
                                                                <select name="guest_gender[]" class="form-select form-select-sm">
                                                                    <option value="">-</option>
                                                                    <option value="F" <?= ($g['gender'] ?? '') === 'F' ? 'selected' : '' ?>>หญิง</option>
                                                                    <option value="M" <?= ($g['gender'] ?? '') === 'M' ? 'selected' : '' ?>>ชาย</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                    name="guest_phone[]"
                                                                    class="form-control form-control-sm"
                                                                    value="<?= h($g['guest_phone'] ?? '') ?>"
                                                                    placeholder="เบอร์ (ถ้ามี)">
                                                            </td>
                                                            <td class="text-end">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-danger btnRemoveGuest"
                                                                    title="ลบรายชื่อ">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <!-- Empty state -->
                                                    <tr class="guest-empty">
                                                        <td colspan="5" class="py-4">
                                                            <div class="d-flex flex-column align-items-center text-center">
                                                                <div class="mb-2 text-muted">
                                                                    <i class="far fa-address-card fa-2x"></i>
                                                                </div>
                                                                <div class="fw-semibold">ยังไม่มีรายชื่อผู้เข้าพัก</div>
                                                                <div class="text-muted small">กด “เพิ่มรายชื่อ” เพื่อเริ่มกรอกข้อมูล</div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Callout -->
                                    <div class="alert alert-light border mt-3 mb-0">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fas fa-info-circle mt-1"></i>
                                            <div class="small">
                                                ถ้าต้องการให้รายชื่อมีผลกับการจัดห้อง ให้กด <span class="fw-semibold">“จัดสรรห้องใหม่”</span> หลังบันทึก
                                            </div>
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

    <?php include_once PARTIALS_PATH . '/admin/script_admin.php'; ?>
    <script src="/assets/js/admin/ad_edit_booking.js"></script>
</body>

</html>