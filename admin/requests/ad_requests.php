<?php
require_once __DIR__ . '/../../utils/admin_guard.php';
require_once '../../db.php';
require_once '../../utils/booking_helper.php';

$activeMenu = 'requests';
$pageTitle = 'รายการจองห้องพัก';
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
                        <div class="card-header text-white d-flex justify-content-between align-items-center">
                            <h1 class="card-title mb-0">รายการคำขอ</h1>
                            <a href="ad_month_report.php" class="btn btn-light btn-sm ms-auto fs-6">
                                <i class="fas fa-file-alt me-1 fs-6"></i> รายงานประจำเดือน
                            </a>
                        </div>
                        <div class="card-body">
                            <table id="bookingsTable" class="table table-bordered table-striped table-requests text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>เลขที่ใบจอง</th>
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
                                            $bookingCode = formatBookingCode($row['id'] ?? null);

                                            echo "<tr data-id='{$row['id']}' data-status='{$status}' data-reason='" . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . "'>";
                                            echo "<td>{$bookingCode}</td>";
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
                                            } else { // rejected
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
                <div class="modal-footer d-flex justify-content-between" id="detailFooter">
                    <button type="button" class="btn btn-danger d-none" id="btnDeleteBooking">
                        <i class="fas fa-trash-alt"></i> ลบรายการจอง
                    </button>

                    <!-- <div class="ms-auto">
                        <button type="button" class="btn btn-primary d-none" id="btnEditBooking">
                            <i class="fas fa-edit"></i> แก้ไขข้อมูล
                        </button>
                    </div> -->
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