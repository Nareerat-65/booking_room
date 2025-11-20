<?php
$pageTitle = 'ติดต่อเรา - ระบบจองห้องพัก';
$extraHead = '
    <style>
        .contact-card {
            border-radius: 1.25rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .contact-header {
            background-color: #F57B39;
            color: white;
            padding: 1.2rem;
        }
        .btn-primary {
            background-color: #F57B39;
            border: none;
            border-radius: 999px;
        }
        .btn-primary:hover { opacity: 0.9; }
    </style>
';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include 'partials/head_user.php'; ?>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold py-2 d-flex align-items-center" href="#">
                <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png" alt="Logo" width="80" height="80" class="me-3">
                <span style="line-height:1; font-size:1.8rem;">
                    ระบบจองห้องพัก
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link " href="index.php">หน้าแรก</a></li>
                    <li class="nav-item"><a class="nav-link" href="u_booking.php">จองห้องพัก</a></li>
                    <li class="nav-item"><a class="nav-link active" href="u_contact.php">ติดต่อเรา</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="text-center mb-4">ติดต่อเรา</h2>

        <div class="row g-4">

            <!-- ช่องทางติดต่อ -->
            <div class="col-md-4">
                <div class="contact-card bg-white p-4">
                    <h5><b>ข้อมูลการติดต่อ</b></h5>
                    <hr>
                    <p><b>📍 ที่อยู่:</b><br>
                        หน่วยงานกิจการนิสิต คณะแพทยศาสตร์ มหาวิทยาลัยนเรศวร เลขที่ 99 หมู่ 9 ตำบลท่าโพธิ์ อำเภอเมืองพิษณุโลก จังหวัดพิษณุโลก รหัสไปรษณีย์ 65000</p>

                    <p><b>📞 โทรศัพท์:</b><br>
                        0-5596-7847</p>

                    <p><b>📧 Email:</b><br>
                        dormitory@nu.ac.th</p>

                    <p><b>⏰ เวลาทำการ:</b><br>
                        จันทร์–ศุกร์ 08:30 – 16:30 น.</p>
                </div>
            </div>

            <!-- แผนที่ -->
            <div class="col-md-8">
                <div class="contact-card">
                    <div class="contact-header">
                        <h5>แผนที่ตั้ง</h5>
                    </div>

                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3820.5597672934327!2d100.18645437495645!3d16.748803620887543!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30dfbea1490380a5%3A0x1d7087a8039d6b1!2z4LmC4Lij4LiH4Lie4Lii4Liy4Lia4Liy4Lil4Lih4Lir4Liy4Lin4Li04LiX4Lii4Liy4Lil4Lix4Lii4LiZ4LmA4Lij4Lio4Lin4Lij!5e0!3m2!1sth!2sth!4v1763451614018!5m2!1sth!2sth"
                        width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

        </div>

        <!-- ฟอร์มส่งข้อความ -->
        <div class="contact-card bg-white p-4 mt-5">
            <h5><b>ส่งข้อความถึงเรา</b></h5>
            <form>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">ชื่อของคุณ</label>
                        <input type="text" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">ข้อความ</label>
                        <textarea class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="col-12 mt-3">
                        <button class="btn btn-primary px-4">ส่งข้อความ</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>