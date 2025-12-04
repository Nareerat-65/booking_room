<?php
require_once __DIR__ . '/../../utils/admin_guard.php';

function formatDate(string $dateStr): string
{
    $date = new DateTime($dateStr);
    return $date->format('d/m/Y');
}

function formatPosition(array $row): string
{
    $pos = $row['position'] ?? '';
    switch ($pos) {
        case 'student':
            $year = isset($row['student_year']) && $row['student_year'] !== ''
                ? $row['student_year'] : '–';
            return "นักศึกษา/นิสิตแพทย์ชั้นปีที่ {$year}";
        case 'intern':
            return 'แพทย์ใช้ทุน';
        case 'resident':
            return 'แพทย์ประจำบ้าน';
        case 'staff':
            return 'เจ้าหน้าที่';
        case 'other':
            $other = trim($row['position_other'] ?? '');
            return $other !== '' ? $other : 'อื่น ๆ';
        default:
            return '–';
    }
}

function formatPurpose(array $row): string
{
    if (($row['purpose'] ?? '') === 'study') {
        $course = trim($row['study_course'] ?? '');
        return $course !== ''
            ? "ศึกษารายวิชา {$course}"
            : "ศึกษารายวิชา (ไม่ระบุชื่อวิชา)";
    }
    return $row['purpose'] ? $row['purpose'] : '-';
}

require_once '../../db.php';
$activeMenu = 'requests';
$pageTitle = 'รายการคำขอ';
$extraHead = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="/assets/css/admin/ad_requests.css">
';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../../partials/admin/head_admin.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <?php include_once '../../partials/admin/nav_admin.php'; ?>
        <?php include_once '../../partials/admin/sidebar_admin.php'; ?>

        <!-- ===== Main Content (v4) ===== -->
        <main class="app-main">
            <div class="app-content-header py-3">
                <div class="container-fluid text-center">
                    <h2 class="my-3">📋 รายการคำขอจองห้องพัก</h2>
                    <p class="text-muted mb-2">
                        ตรวจสอบคำขอ, อนุมัติ หรือระบุเหตุผลที่ไม่อนุมัติได้จากหน้านี้
                    </p>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header text-white">
                            <h1 class="card-title mb-0">รายการคำขอ</h1>
                        </div>

                        <div class="card-body">
                            <table id="bookingsTable" class="table table-bordered table-striped table-requests text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ชื่อผู้จอง</th>
                                        <th>เบอร์โทร</th>
                                        <th>ID Line</th>
                                        <th>Email</th>
                                        <th>ตำแหน่ง</th>
                                        <th>หน่วยงาน</th>
                                        <th>วัตถุประสงค์</th>
                                        <th>ภาควิชา</th>
                                        <th>วันที่เข้าพัก</th>
                                        <th>วันที่ออก</th>
                                        <th>จำนวนคน</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "
                                        SELECT b.*,
                                            (
                                                SELECT d.file_path
                                                FROM booking_documents d
                                                WHERE d.booking_id = b.id
                                                    AND d.uploaded_by = 'admin'
                                                    AND d.is_visible_to_user = 1
                                                ORDER BY d.uploaded_at DESC
                                                LIMIT 1
                                            ) AS admin_doc_path
                                        FROM bookings b
                                        ORDER BY b.id DESC
                                    ";
                                    $result = $conn->query($sql);
                                    if ($result && $result->num_rows > 0) {
                                        $i = 1;

                                        while ($row = $result->fetch_assoc()) {
                                            $status = $row['status'] ?? 'pending';
                                            $reason = $row['reject_reason'] ?? '';
                                            $docPath = $row['admin_doc_path'] ?? '';

                                            echo "<tr data-id='{$row['id']}' data-status='{$status}' data-reason='" . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . "'>";
                                            echo "<td>{$i}</td>";
                                            echo "<td>" . htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['line_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars(formatPosition($row), ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars(formatPurpose($row), ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['study_dept'] ?: ($row['elective_dept'] ?: '-'), ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars(formatDate($row['check_in_date']), ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars(formatDate($row['check_out_date']), ENT_QUOTES, 'UTF-8') . "</td>";

                                            $w = (int)$row['woman_count'];
                                            $m = (int)$row['man_count'];
                                            $people = [];
                                            if ($w > 0) $people[] = "หญิง {$w}";
                                            if ($m > 0) $people[] = "ชาย {$m}";
                                            if (empty($people)) $people[] = "-";
                                            echo "<td>" . implode(" ", $people) . "</td>";

                                            if ($status == 'approved') {
                                                $badge = '<span class="badge text-bg-success">อนุมัติแล้ว</span>';
                                            } elseif ($status == 'rejected') {
                                                $badge = '<span class="badge text-bg-danger">ไม่อนุมัติ</span>';
                                            } else {
                                                $badge = '<span class="badge text-bg-warning text-dark">รออนุมัติ</span>';
                                            }
                                            echo "<td>{$badge}</td>";

                                            echo '<td>';
                                            if ($status === 'pending') {
                                                echo "
                                            <button class='btn btn-success mb-1 btn-sm btn-approve'>อนุมัติ</button>
                                            <button class='btn btn-danger btn-sm btn-reject'
                                                    data-bs-toggle='modal' data-bs-target='#rejectModal'>
                                                ไม่อนุมัติ
                                            </button>
                                        ";
                                            } elseif ($status === 'approved') {
                                                if ($docPath) {
                                                    $safePath = htmlspecialchars($docPath, ENT_QUOTES, 'UTF-8');
                                                    echo "
                                                <button class='btn btn-primary btn-sm btn-view-doc mb-1'
                                                        data-doc='{$safePath}'>
                                                    <i class='fas fa-file-alt'></i> ดูเอกสาร
                                                </button>
                                                <button class='btn btn-warning btn-sm btn-upload-doc ms-1'
                                                        data-id='{$row['id']}'>
                                                    <i class='fas fa-cog'></i> แก้ไข
                                                </button>
                                            ";
                                                } else {
                                                    echo "
                                                <button class='btn btn-success btn-sm btn-upload-doc'
                                                        data-id='{$row['id']}'>
                                                    <i class='fas fa-upload'></i> อัปโหลด
                                                </button>
                                            ";
                                                }
                                            } else {
                                                echo "
                                            <button class='btn btn-outline-secondary btn-sm btn-detail'
                                                    data-id='{$row['id']}'
                                                    data-status='{$status}'
                                                    data-reason='" . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . "'>
                                                <i class='fas fa-info-circle'></i> รายละเอียด
                                            </button>
                                        ";
                                            }
                                            echo '</td>';

                                            echo "</tr>";
                                            $i++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='14' class='text-center text-muted'>ไม่มีข้อมูลการจอง</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include_once '../../partials/admin/footer_admin.php'; ?>

    </div>

    <!-- ===== Modals (Bootstrap 5) ===== -->

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">ระบุเหตุผลที่ไม่อนุมัติ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectForm">
                        <div class="mb-3">
                            <label for="reason" class="form-label">เหตุผล:</label>
                            <textarea class="form-control" id="reason" name="reason" rows="4"
                                placeholder="กรอกเหตุผลที่ไม่อนุมัติ..."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-danger">ส่งเหตุผล</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white" id="detailHeader">
                    <h5 class="modal-title" id="detailTitle">รายละเอียดคำขอ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailBody"></div>
            </div>
        </div>
    </div>

    <!-- Upload Doc Modal -->
    <div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="uploadForm" action="ad_upload_document.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">อัปโหลดเอกสารประกอบการจอง</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="booking_id" id="uploadBookingId">

                        <div class="mb-3">
                            <label class="form-label">คำขอจอง:</label>
                            <div id="uploadBookingInfo" class="fw-bold text-primary"></div>
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">เลือกไฟล์เอกสาร</label>
                            <input type="file" name="document" id="document" class="form-control" required
                                accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                รองรับไฟล์ .pdf, .jpg, .jpeg, .png ขนาดไม่เกิน 5MB
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success">อัปโหลด</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Doc Modal -->
    <div class="modal fade" id="viewDocModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">เอกสารที่อัปโหลด</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <iframe id="docFrame" src="" width="100%" height="600" style="border:0;"></iframe>
                    <div class="mt-2">
                        <a id="docDownload" href="" target="_blank" class="btn btn-outline-primary btn-sm">
                            ดาวน์โหลด / เปิดในแท็บใหม่
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Scripts ===== -->
    <?php include_once __DIR__ . '/../../partials/admin/script_admin.php'; ?>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="/assets/js/admin/ad_requests.js"></script>

</body>

</html>