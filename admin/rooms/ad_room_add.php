<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once CONFIG_PATH . '/db.php';
require_once UTILS_PATH . '/admin_guard.php';

$error = '';
$room_name = '';
$capacity = '';
$location = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $room_name = trim($_POST['room_name'] ?? '');
    $capacity  = (int)($_POST['capacity'] ?? 0);
    $location  = trim($_POST['location'] ?? '');

    // validate
    if ($room_name === '' || $capacity <= 0) {
        $error = 'กรุณากรอกชื่อห้องและความจุให้ถูกต้อง';
    } else {

        // เช็คชื่อห้องซ้ำ
        $stmt = $conn->prepare(
            "SELECT id FROM rooms WHERE room_name = ? LIMIT 1"
        );
        $stmt->bind_param("s", $room_name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'ชื่อห้องนี้มีอยู่แล้ว';
        } else {

            // เพิ่มห้อง
            $stmt = $conn->prepare(
                "INSERT INTO rooms (room_name, location, capacity, is_active)
                 VALUES (?, ?, ?, 1)"
            );
            $stmt->bind_param("ssi", $room_name, $location, $capacity);
            $stmt->execute();

            header("Location: ad_rooms_list.php");
            exit;
        }
    }
}
$pageTitle  = "เพิ่มห้องพัก";
$activeMenu = "rooms";
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
            <div class="container mt-4" style="max-width:600px">

                <h3 class="mb-3">➕ เพิ่มห้องพัก</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post">

                    <div class="mb-3">
                        <label class="form-label">ชื่อห้อง *</label>
                        <input type="text" name="room_name"
                            class="form-control"
                            value="<?= htmlspecialchars($room_name) ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ที่ตั้ง / อาคาร</label>
                        <input type="text" name="location"
                            class="form-control"
                            placeholder="เช่น อาคาร A ชั้น 2"
                            value="<?= htmlspecialchars($location) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ความจุ (คน) *</label>
                        <input type="number" name="capacity"
                            class="form-control"
                            min="1"
                            value="<?= htmlspecialchars($capacity) ?>"
                            required>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" onclick="saveRoom(event)">บันทึก</button>
                        <a href="ad_rooms_list.php" class="btn btn-secondary">ยกเลิก</a>
                    </div>

                </form>
            </div>
        </main>
        <?php include_once PARTIALS_PATH . '/admin/footer_admin.php'; ?>
    </div>
    <?php include_once PARTIALS_PATH . '/admin/script_admin.php'; ?>

    <script>
        function saveRoom(e) {
            e.preventDefault();
            SA.confirm(
                'ยืนยันการเพิ่มห้องพัก?',
                'คุณแน่ใจหรือไม่ว่าต้องการเพิ่มห้องพักนี้?',
                'เพิ่มห้องพัก',
                'ยกเลิก',
                (isConfirmed) => {
                    if (isConfirmed) {
                        e.target.closest('form').submit();
                    }
                }
            )
        }
    </script>
</body>


</html>