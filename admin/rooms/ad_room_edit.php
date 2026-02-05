<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once CONFIG_PATH . '/db.php';
require_once UTILS_PATH . '/admin_guard.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ad_rooms_list.php");
    exit;
}

// ดึงข้อมูลห้อง
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    header("Location: ad_rooms_list.php");
    exit;
}

$error = '';
$success = '';

// เช็คว่ามี allocation ใช้อยู่ไหม
$stmt = $conn->prepare(
    "SELECT COALESCE(SUM(woman_count + man_count), 0)
     FROM room_allocations
     WHERE room_id = ?
     AND CURDATE() BETWEEN start_date AND end_date"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($current_guest_count);
$stmt->fetch();
$stmt->close();

$current_guest_count = (int)$current_guest_count;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $room_name = trim($_POST['room_name'] ?? '');
    $location  = trim($_POST['location'] ?? '');
    $capacity  = (int)($_POST['capacity'] ?? 0);

    if ($room_name === '' || $capacity <= 0) {
        $error = 'กรุณากรอกชื่อห้องและความจุให้ถูกต้อง';
    } elseif ($current_guest_count > 0 && $capacity < $room['capacity']) {
        $error = 'ไม่สามารถลดความจุได้ เนื่องจากมีผู้เข้าพักอยู่ในห้องขณะนี้';
    } else {

        // เช็คชื่อห้องซ้ำ (ยกเว้นตัวเอง)
        $stmt = $conn->prepare(
            "SELECT id FROM rooms WHERE room_name = ? AND id != ?"
        );
        $stmt->bind_param("si", $room_name, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'ชื่อห้องนี้มีอยู่แล้ว';
        } else {

            // อัปเดตข้อมูล
            $stmt = $conn->prepare(
                "UPDATE rooms 
                 SET room_name = ?, location = ?, capacity = ?
                 WHERE id = ?"
            );
            $stmt->bind_param("ssii", $room_name, $location, $capacity, $id);
            $stmt->execute();

            $success = 'บันทึกข้อมูลเรียบร้อยแล้ว';

            // โหลดข้อมูลใหม่
            $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $room = $stmt->get_result()->fetch_assoc();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขห้องพัก</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-4" style="max-width:650px">

        <h3 class="mb-3">✏️ แก้ไขห้องพัก</h3>
        <small class="text-muted">
            ขณะนี้มีผู้เข้าพัก <?= $current_guest_count ?> คน
        </small>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">ชื่อห้อง *</label>
                <input type="text" name="room_name"
                    class="form-control"
                    value="<?= htmlspecialchars($room['room_name']) ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">ที่ตั้ง / อาคาร</label>
                <input type="text" name="location"
                    class="form-control"
                    value="<?= htmlspecialchars($room['location']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">ความจุ (คน) *</label>
                <input type="number" name="capacity"
                    class="form-control"
                    min="<?= $current_guest_count > 0 ? $room['capacity'] : 1 ?>"
                    value="<?= (int)$room['capacity'] ?>"
                    required>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">บันทึก</button>
                <a href="ad_rooms_list.php" class="btn btn-secondary">กลับ</a>
            </div>

        </form>

    </div>
</body>

</html>