<?php
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'booking_system';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        // ตรวจสอบรหัสผ่าน
        if (hash('sha256', $password) === $row['password']) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['full_name'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = 'รหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $error = 'ไม่พบบัญชีผู้ใช้';
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0f0ff, #ffffff);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }

        .login-card h1 {
            font-size: 1.4rem;
            text-align: center;
            margin-bottom: 1.5rem;
            color: #0d6efd;
        }

        .form-control {
            border-radius: 0.6rem;
        }

        .btn-primary {
            border-radius: 999px;
            width: 100%;
            padding: 0.75rem;
            font-weight: 600;
        }

        .alert {
            border-radius: 0.6rem;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <h1>เข้าสู่ระบบผู้ดูแลระบบ</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" id="username" name="username" required placeholder="ชื่อผู้ใช้...">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="รหัสผ่าน...">
            </div>

            <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
        </form>

        <p class="text-center text-muted mt-3 mb-0">
            <small>© ระบบจองห้องพัก - ผู้ดูแลเท่านั้น</small>
        </p>
    </div>

</body>

</html>