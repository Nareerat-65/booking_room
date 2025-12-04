<?php
require_once __DIR__ . '/../../utils/admin_guard.php';
require_once '../../db.php';

// ดึงรายการจอง + นับจำนวนเอกสาร
$sql = "
    SELECT 
        b.id,
        b.full_name,
        b.department,
        b.check_in_date,
        b.check_out_date,
        b.status,
        COUNT(d.id) AS doc_count
    FROM bookings b
    LEFT JOIN booking_documents d ON d.booking_id = b.id
    GROUP BY b.id
    ORDER BY b.created_at DESC
";
$res = $conn->query($sql);

$pageTitle  = "จัดการเอกสาร";
$activeMenu = "documents";
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../../partials/admin/head_admin.php'; ?>
</head>

<body class="layout-fixed">
    <div class="app-wrapper">
        <?php include '../../partials/admin/nav_admin.php'; ?>
        <?php include '../../partials/admin/sidebar_admin.php'; ?>

        <main class="app-main">
            <div class="app-content-header py-3">
                <div class="container-fluid text-center">
                    <h2 class="my-3">📄 จัดการเอกสาร</h2>
                    <p class="text-muted mb-2">
                        สามารถอัปโหลดและจัดการเอกสารได้จากหน้านี้
                    </p>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">เลือกรายการจองเพื่อจัดการเอกสาร</h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="docBookingTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>เลขที่รายการ</th>
                                        <th>ชื่อผู้จอง</th>
                                        <th>หน่วยงานต้นสังกัด</th>
                                        <th>วันที่เข้าพัก</th>
                                        <th>วันที่ย้ายออก</th>
                                        <th>จำนวนเอกสาร</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $res->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?= (int)$row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                                            <td><?= htmlspecialchars($row['department']) ?></td>
                                            <td><?= htmlspecialchars($row['check_in_date']) ?></td>
                                            <td><?= htmlspecialchars($row['check_out_date']) ?></td>
                                            <td><?= (int)$row['doc_count'] ?></td>
                                            <td>
                                                <a href="ad_doc_manage.php?booking_id=<?= (int)$row['id'] ?>"
                                                    class="btn btn-sm btn-primary">
                                                    จัดการเอกสาร
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

        <?php include '../../partials/admin/footer_admin.php'; ?>
    </div>
        <?php include_once __DIR__ . '/../../partials/admin/script_admin.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery && $.fn.DataTable) {
                $('#docBookingTable').DataTable();
            }
        });
    </script>
</body>

</html>