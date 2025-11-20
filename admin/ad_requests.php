<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ad_login.php');
    exit;
}

$pageTitle = 'รายการคำขอจองห้องพัก';
$extraHead = ''; // ใช้ DataTables ผ่าน CSS จาก head_admin.php แล้ว
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include 'partials/head_admin.php'; ?>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- TOP NAVBAR -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <!-- Left: ปุ่ม toggle sidebar + title -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link font-weight-bold"> รายการคำขอจองห้องพัก </span>
                </li>
            </ul>

            <!-- Right: admin name + logout -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-flex align-items-center">
                    <span class="navbar-text mr-3">
                        <?= htmlspecialchars($_SESSION['admin_name']) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="ad_logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /TOP NAVBAR -->

        <!-- SIDEBAR -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="ad_dashboard.php" class="brand-link d-flex align-items-center">
                <img src="../img/Medicine_Naresuan.png" alt="Logo" class="brand-image img-circle elevation-3"
                    style="opacity:.9">
                <span class="brand-text font-weight-light ml-2">Admin Dashboard</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User info -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-white"></i>
                    </div>
                    <div class="info">
                        <span class="d-block text-white"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                    </div>
                </div>

                <!-- Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                        <li class="nav-item">
                            <a href="ad_dashboard.php" class="nav-link">
                                <!-- แนะนำเปลี่ยนเป็น icon ที่มีจริงใน Font Awesome -->
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ad_requests.php" class="nav-link active">
                                <i class="nav-icon fas fa-list"></i>
                                <p>รายการคำขอจองห้องพัก</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ad_calendar.php" class="nav-link ">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>ปฏิทินห้องพัก</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ad_change_password.php" class="nav-link">
                                <i class="nav-icon fas fa-key"></i>
                                <p>เปลี่ยนรหัสผ่าน</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ad_logout.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>ออกจากระบบ</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
            <!-- /Sidebar -->
        </aside>
        <!-- /SIDEBAR -->

        <!-- CONTENT WRAPPER -->
        <div class="content-wrapper">
            <!-- Header -->
            <section class="content-header">
                <div class="container-fluid text-center ">
                    <h1 class="my-3">📋 รายการคำขอจองห้องพัก</h1>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                    <div class="card">
                        <div class="card-header text-white">
                            <h1 class="card-title mb-0">รายการคำขอ</h1>
                        </div>
                        <div class="card-body">
                            <table id="bookingsTable" class="table table-bordered table-striped">
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
                                    require_once '../db.php';

                                    $sql = "SELECT * FROM bookings ORDER BY id DESC";
                                    $result = $conn->query($sql);
                                    if ($result && $result->num_rows > 0) {
                                        $i = 1;
                                        function formatPosition(array $row): string
                                        {
                                            $pos = $row['position'] ?? '';
                                            switch ($pos) {
                                                case 'student':
                                                    $year = isset($row['student_year']) && $row['student_year'] !== ''
                                                        ? $row['student_year'] : '–';
                                                    return "นักศึกษา/นิสิตแพทย์ชั้นปีที่ {$year}";
                                                case 'doctor':
                                                    return 'แพทย์';
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

                                        while ($row = $result->fetch_assoc()) {
                                            $status = $row['status'] ?? 'pending';
                                            $reason = $row['reject_reason'] ?? '';
                                            echo "<tr data-id='{$row['id']}' data-status='{$status}' data-reason='" . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . "'>";
                                            echo "<td>{$i}</td>";
                                            echo "<td>{$row['full_name']}</td>";
                                            echo "<td>{$row['phone']}</td>";
                                            echo "<td>{$row['line_id']}</td>";
                                            echo "<td>{$row['email']}</td>";
                                            echo "<td>" . htmlspecialchars(formatPosition($row), ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>{$row['department']}</td>";
                                            echo "<td>" . htmlspecialchars(formatPurpose($row), ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars(
                                                $row['study_dept'] ?: ($row['elective_dept'] ?: '-'),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) . "</td>";
                                            echo "<td>{$row['check_in_date']}</td>";
                                            echo "<td>{$row['check_out_date']}</td>";

                                            $w = (int)$row['woman_count'];
                                            $m = (int)$row['man_count'];
                                            $people = [];
                                            if ($w > 0) $people[] = "หญิง {$w}";
                                            if ($m > 0) $people[] = "ชาย {$m}";
                                            if (empty($people)) $people[] = "-";
                                            echo "<td>" . implode(" ", $people) . "</td>";

                                            if ($status == 'approved') {
                                                $badge = '<span class="badge badge-success">อนุมัติแล้ว</span>';
                                            } elseif ($status == 'rejected') {
                                                $badge = '<span class="badge badge-danger">ไม่อนุมัติ</span>';
                                            } else {
                                                $badge = '<span class="badge badge-warning text-dark">รออนุมัติ</span>';
                                            }
                                            echo "<td>{$badge}</td>";

                                            echo '<td>';
                                            if ($status === 'pending') {
                                                echo "
                                            <button class='btn btn-success btn-sm btn-approve'>อนุมัติ</button>
                                            <button class='btn btn-danger btn-sm btn-reject' data-toggle='modal' data-target='#rejectModal'>ไม่อนุมัติ</button>
                                        ";
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
                                        echo "<tr><td colspan='13' class='text-center text-muted'>ไม่มีข้อมูลการจอง</td></tr>";
                                    }
                                    $conn->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        </div>
        <!-- /CONTENT WRAPPER -->

        <!-- FOOTER -->
        <footer class="main-footer text-sm">
            <div class="float-right d-none d-sm-inline">
                ระบบจองห้องพัก
            </div>
            <strong>&copy; <?= date('Y'); ?> คณะ/หน่วยงานของคุณ</strong> สงวนลิขสิทธิ์
        </footer>

    </div>

    <!-- MODALS จากไฟล์เดิมของคุณ -->
    <!-- Modal: เหตุผลการไม่อนุมัติ -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">ระบุเหตุผลที่ไม่อนุมัติ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="rejectForm">
                        <div class="mb-3">
                            <label for="reason" class="form-label">เหตุผล:</label>
                            <textarea class="form-control" id="reason" name="reason" rows="4"
                                placeholder="กรอกเหตุผลที่ไม่อนุมัติ..."></textarea>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-danger">ส่งเหตุผล</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: รายละเอียด -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white" id="detailHeader">
                    <h5 class="modal-title" id="detailTitle">รายละเอียดคำขอ</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detailBody">
                    <!-- เติมด้วย JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal โหลด -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content d-flex flex-column justify-content-center align-items-center p-4">
                <div class="spinner-border text-primary mb-3 mx-auto" role="status"></div>
                <div class="text-center">กำลังส่งข้อมูล...<br>กรุณารอสักครู่</div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(function() {
            $('#bookingsTable').DataTable({
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
                    info: "แสดง _START_–_END_ จากทั้งหมด _TOTAL_ รายการ",
                    zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
                    paginate: {
                        first: "หน้าแรก",
                        last: "หน้าสุดท้าย",
                        next: "ถัดไป",
                        previous: "ก่อนหน้า"
                    }
                },
                pageLength: 10,
                order: []
            });

            // ===== logic เดิม =====

            let selectedId = null;

            // อนุมัติ
            $('#bookingsTable').on('click', '.btn-approve', function() {
                const $tr = $(this).closest('tr');
                const id = $tr.data('id');
                updateStatus(id, 'approved');
            });

            // ไม่อนุมัติ — เปิด modal เก็บเหตุผล
            $('#bookingsTable').on('click', '.btn-reject', function() {
                selectedId = $(this).closest('tr').data('id');
                $('#rejectModal').modal('show');
            });

            $('#rejectForm').on('submit', function(e) {
                e.preventDefault();
                const reason = $('#reason').val().trim();
                if (!reason) {
                    alert('กรุณากรอกเหตุผลก่อนส่ง');
                    return;
                }
                updateStatus(selectedId, 'rejected', reason);
                $('#rejectModal').modal('hide');
                $('#reason').val('');
            });

            // รายละเอียด
            $('#bookingsTable').on('click', '.btn-detail', function() {
                const $tr = $(this).closest('tr');
                openDetailModalFromRow($tr);
            });

            function updateStatus(id, status, reason = null) {
                $('#loadingModal').modal('show');

                $.post('ad_updateStatus.php', {
                    id,
                    status,
                    reason
                }, function(res) {
                    if (res === 'success') {
                        const $tr = $(`#bookingsTable tr[data-id="${id}"]`);
                        const $statusCell = $tr.find('td').eq(12);

                        if (status === 'approved') {
                            $statusCell.html('<span class="badge badge-success">อนุมัติแล้ว</span>');
                        } else if (status === 'rejected') {
                            $statusCell.html('<span class="badge badge-danger">ไม่อนุมัติ</span>');
                        } else {
                            $statusCell.html('<span class="badge badge-warning text-dark">รออนุมัติ</span>');
                        }

                        $tr.attr('data-status', status);
                        if (reason !== null) $tr.attr('data-reason', reason);

                        const $actionCell = $tr.find('td').last();
                        $actionCell.html(`
                        <button class="btn btn-outline-secondary btn-sm btn-detail" data-id="${id}">
                            <i class="fas fa-info-circle"></i> รายละเอียด
                        </button>
                    `);

                        openDetailModalFromRow($tr);
                    } else {
                        alert('เกิดข้อผิดพลาดในการอัปเดต');
                    }
                }).fail(function() {
                    alert('เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ');
                }).always(function() {
                    $('#loadingModal').modal('hide');
                });
            }

            function openDetailModalFromRow($tr) {
                const status = ($tr.data('status') || '').toString();
                const reason = ($tr.data('reason') || '').toString();

                const name = $tr.find('td').eq(1).text().trim();
                const inDate = $tr.find('td').eq(9).text().trim();
                const outDate = $tr.find('td').eq(10).text().trim();
                const ppl = $tr.find('td').eq(11).text().trim();

                const $header = $('#detailHeader');
                $header.removeClass('bg-success bg-danger bg-secondary');

                let title = 'รายละเอียดคำขอ';
                if (status === 'approved') {
                    $header.addClass('bg-success');
                    title = 'รายละเอียดคำขอ (อนุมัติแล้ว)';
                } else if (status === 'rejected') {
                    $header.addClass('bg-danger');
                    title = 'รายละเอียดคำขอ (ไม่อนุมัติ)';
                } else {
                    $header.addClass('bg-secondary');
                }
                $('#detailTitle').text(title);

                let html = `
                <div class="mb-2"><b>ชื่อผู้จอง:</b> ${name}</div>
                <div class="mb-2"><b>วันที่เข้าพัก:</b> ${inDate}</div>
                <div class="mb-2"><b>วันที่ออก:</b> ${outDate}</div>
                <div class="mb-2"><b>จำนวนคน:</b> ${ppl}</div>
            `;
                if (status === 'rejected') {
                    html += `<div class="alert alert-danger mt-3"><b>เหตุผลที่ไม่อนุมัติ:</b> ${reason || '—'}</div>`;
                }

                $('#detailBody').html(html);
                $('#detailsModal').modal('show');
            }
        });
    </script>

</body>

</html>