<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดผู้ดูแล</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4">
        <span class="navbar-brand">แดชบอร์ดผู้ดูแล</span>
        <div class="d-flex align-items-center text-white">
            <?= htmlspecialchars($_SESSION['admin_name']) ?>
            <a href="logout.php" class="btn btn-outline-light btn-sm ms-3">ออกจากระบบ</a>
        </div>
    </nav>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h1 class="card-title">📋 รายการคำขอจองห้องพัก</h1>
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
                    require_once '../db.php'; // ไฟล์เชื่อม MySQL ของคุณ

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
                            // ถ้าไม่ใช่ study ก็แสดงค่าดิบหรือเครื่องหมายขีด
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

                            $status = $row['status'] ?? 'pending';
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
                                // ยังไม่ตัดสินใจ → แสดง อนุมัติ/ไม่อนุมัติ
                                echo "
      <button class='btn btn-success btn-sm btn-approve'>อนุมัติ</button>
      <button class='btn btn-danger btn-sm btn-reject' data-toggle='modal' data-target='#rejectModal'>ไม่อนุมัติ</button>
    ";
                            } else {
                                // อนุมัติหรือไม่อนุมัติแล้ว → แสดงปุ่มรายละเอียดแทน
                                // เก็บ reason ใน data-* ด้วย (กรณี rejected)
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
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="กรอกเหตุผลที่ไม่อนุมัติ..."></textarea>
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

            // อนุมัติ
            $('#bookingsTable').on('click', '.btn-approve', function() {
                const $tr = $(this).closest('tr');
                const id = $tr.data('id');
                updateStatus(id, 'approved');
            });

            // ไม่อนุมัติ — เปิด modal เก็บเหตุผล
            let selectedId = null;
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

            // ===== ฟังก์ชัน =====
            function updateStatus(id, status, reason = null) {
                $.post('ad_updateStatus.php', {
                    id,
                    status,
                    reason
                }, function(res) {
                    if (res === 'success') {
                        const $tr = $(`#bookingsTable tr[data-id="${id}"]`);
                        const $statusCell = $tr.find('td').eq(12); // คอลัมน์ "สถานะ"

                        if (status === 'approved') {
                            $statusCell.html('<span class="badge badge-success">อนุมัติแล้ว</span>');
                        } else if (status === 'rejected') {
                            $statusCell.html('<span class="badge badge-danger">ไม่อนุมัติ</span>');
                        } else {
                            $statusCell.html('<span class="badge badge-warning text-dark">รออนุมัติ</span>');
                        }

                        // อัปเดต data-* บนแถว
                        $tr.attr('data-status', status);
                        if (reason !== null) $tr.attr('data-reason', reason);

                        // แทนที่ปุ่มในคอลัมน์สุดท้ายด้วย "รายละเอียด"
                        const $actionCell = $tr.find('td').last();
                        $actionCell.html(`
          <button class="btn btn-outline-secondary btn-sm btn-detail" data-id="${id}">
            <i class="fas fa-info-circle"></i> รายละเอียด
          </button>
        `);

                        // (เลือกได้) เปิด modal รายละเอียดให้ดูทันที
                        openDetailModalFromRow($tr);
                    } else {
                        alert('เกิดข้อผิดพลาดในการอัปเดต');
                    }
                }).fail(function() {
                    alert('เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ');
                });
            }

            function openDetailModalFromRow($tr) {
                const status = ($tr.data('status') || '').toString();
                const reason = ($tr.data('reason') || '').toString();

                // ดึงค่าจากเซลล์ในแถว (ปรับ index ตามหัวตารางของคุณ)
                const name = $tr.find('td').eq(1).text().trim();
                const inDate = $tr.find('td').eq(9).text().trim();
                const outDate = $tr.find('td').eq(10).text().trim();
                const ppl = $tr.find('td').eq(11).text().trim();

                // ตั้งหัว modal และสีตามสถานะ
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

                // เนื้อหาใน modal
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