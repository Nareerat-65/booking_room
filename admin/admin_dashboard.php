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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4">
        <span class="navbar-brand">แดชบอร์ดผู้ดูแล</span>
        <div class="d-flex align-items-center text-white">
            <?= htmlspecialchars($_SESSION['admin_name']) ?>
            <a href="logout.php" class="btn btn-outline-light btn-sm ms-3">ออกจากระบบ</a>
        </div>
    </nav>
    <div class="container-fluid py-5 px-3">
        <h3>ยินดีต้อนรับคุณ <?= htmlspecialchars($_SESSION['admin_name']) ?></h3>
        <p>นี่คือหน้าแดชบอร์ดของผู้ดูแลระบบ</p>
        <h2 class="fw-bold text-primary mb-4">📋 รายการคำขอจองห้องพัก</h2>

        <div class="table-wrapper">
            <div class="table-responsive">
                <table class="table table-bordered align-middle table-hover">
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
                            <th>วันที่เข้าพัก</th>
                            <th>วันที่ออก</th>
                            <th>จำนวนคน</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>นางสาวนรีรัตน์ ศรีแก้วอินทร์</td>
                            <td>0812345678</td>
                            <td>nareerat_line</td>
                            <td>nareerat@example.com</td>
                            <td>นักศึกษา</td>
                            <td>ภาควิชาอายุรศาสตร์</td>
                            <td>ศึกษารายวิชา</td>
                            <td>2025-11-15</td>
                            <td>2025-11-20</td>
                            <td>หญิง 1 ชาย 3</td>
                            <td><span class="badge bg-warning text-dark badge-status">รออนุมัติ</span></td>
                            <td>
                                <button class="btn btn-success btn-sm">✅ อนุมัติ</button>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">❌ ปฏิเสธ</button>
                            </td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <td>นายสมชาย ใจดี</td>
                            <td>0899998888</td>
                            <td>somchai_line</td>
                            <td>somchai@example.com</td>
                            <td>แพทย์</td>
                            <td>ศัลยศาสตร์</td>
                            <td>Elective</td>
                            <td>2025-12-01</td>
                            <td>2025-12-10</td>
                            <td>ชาย 1</td>
                            <td><span class="badge bg-success badge-status">อนุมัติแล้ว</span></td>
                            <td>
                                <button class="btn btn-outline-secondary btn-sm">ดูรายละเอียด</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal: เหตุผลการไม่อนุมัติ -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>