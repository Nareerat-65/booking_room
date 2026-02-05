<?php
// admin/rooms/rooms_list.php
require_once dirname(__DIR__, 2) . '/config.php';
require_once CONFIG_PATH . '/db.php';
require_once UTILS_PATH . '/admin_guard.php';


$sql = "SELECT * FROM rooms ORDER BY room_name ASC";
$result = $conn->query($sql);

$pageTitle  = "จัดการห้องพัก";
$activeMenu = "rooms";
$extraHead = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="/assets/css/admin/ad_rooms_list.css">
';
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
                <div class="container-fluid text-center">
                    <h2 class="my-3">🏢 จัดการห้องพัก</h2>
                    <p class="text-muted mb-2">
                        จัดการข้อมูลห้องพักทั้งหมดในระบบ
                    </p>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center">
                            <h1 class="card-title mb-0">รายการห้องพักทั้งหมด</h1>
                            <a href="ad_room_add.php" class="btn btn-light btn-sm ms-auto fs-6">
                                <i class="fas fa-plus me-1 fs-6"></i> เพิ่มห้องพักใหม่
                            </a>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="docBookingTable" class="table table-bordered table-striped table-requests text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ชื่อห้อง</th>
                                        <th>ที่ตั้ง</th>
                                        <th>ความจุ (คน)</th>
                                        <th>สถานะ</th>
                                        <th width="260">การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php $i = 1; ?>
                                        <?php while ($room = $result->fetch_assoc()): ?>
                                            <tr class="text-center">
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($room['room_name']) ?></td>
                                                <td><?= htmlspecialchars($room['location']) ?></td>
                                                <td><?= $room['capacity'] ?></td>

                                                <td>
                                                    <?php if ($room['is_active']): ?>
                                                        <span class="badge bg-success">เปิดใช้งาน</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">ปิดใช้งาน</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <a href="ad_room_edit.php?id=<?= $room['id'] ?>"
                                                        class="btn btn-warning btn-sm">
                                                        ✏️ แก้ไข
                                                    </a>

                                                    <?php if ($room['is_active']): ?>
                                                        <a href="ad_room_toggle.php?id=<?= $room['id'] ?>&action=off"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('ต้องการปิดห้องนี้ใช่หรือไม่?');">
                                                            ปิดห้อง
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="ad_room_toggle.php?id=<?= $room['id'] ?>&action=on"
                                                            class="btn btn-success btn-sm">
                                                            เปิดห้อง
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                ยังไม่มีข้อมูลห้องพัก
                                            </td>
                                        </tr>
                                    <?php endif; ?>
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

</body>

</html>