<?php
$pageTitle = 'ระบบจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="../assets/css/user/index.css">';
$activeMenu = 'index';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/user/head_user.php'; ?>
</head>

<body>
    <!-- navbar -->
    <?php include '../partials/user/nav_user.php'; ?>

    <!-- ชื่อระบบ -->
    <div class="container-fluid hero px-0">
        <h1 class="mb-3">ระบบจองห้องพักสำหรับนิสิตแพทย์ / แพทย์ / บุคลากร</h1>
        <p class=" text-white mb-4">
            กรอกคำขอจองออนไลน์ รอการอนุมัติผ่านอีเมล และรับลิงก์สำหรับกรอกรายชื่อผู้เข้าพักในแต่ละห้อง
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="u_booking.php" class="btn btn-main">เริ่มจองห้องพัก</a>
            <a href="#steps" class="btn btn-outline-light rounded-pill">ดูขั้นตอนการจอง</a>
        </div>
    </div>

    <!-- ขั้นตอนการจอง -->
    <div id="steps" class="container pb-4 mt-4 ">
        <h2 class="h4 mb-3 text-center mb-4">ขั้นตอนการจอง</h2>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card step-card h-100 p-3">
                    <h5>1. ส่งคำขอจอง</h5>
                    <p class="small text-muted mb-0">
                        กรอกแบบฟอร์มข้อมูลส่วนตัว จำนวนผู้เข้าพัก และช่วงวันที่ต้องการเข้าพักผ่านหน้า “จองห้องพัก”
                    </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card step-card h-100 p-3">
                    <h5>2. เจ้าหน้าที่พิจารณา</h5>
                    <p class="small text-muted mb-0">
                        เจ้าหน้าที่ตรวจสอบห้องว่างและอนุมัติ/ไม่อนุมัติคำขอของคุณ
                    </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card step-card h-100 p-3">
                    <h5>3. รับผลทางอีเมล</h5>
                    <p class="small text-muted mb-0">
                        หากอนุมัติ ระบบจะส่งอีเมลพร้อมลิงก์ให้คุณเข้าไปกรอกรายชื่อผู้เข้าพักในแต่ละห้อง
                    </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card step-card h-100 p-3">
                    <h5>4. เข้าพักตามกำหนด</h5>
                    <p class="small text-muted mb-0">
                        เข้าพักตามวันที่นัดหมาย และปฏิบัติตามกฎระเบียบของหอพัก/หน่วยงาน
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ประกาศ / เงื่อนไข -->
    <div class="container pb-5">
        <div class="alert alert-warning ">
            <strong>ประกาศ / เงื่อนไขการเข้าพัก</strong><br>
            - กรุณาส่งคำขอจองล่วงหน้าอย่างน้อย 2 สัปดาห์ก่อนวันที่เข้าพัก<br>
            - ห้องพัก 1 ห้องเข้าพักได้สูงสุด 4 คน แยกห้องตามเพศ (ชาย/หญิง)<br>
            - หลังย้ายออกจะมีการทำความสะอาดห้อง 3 วัน ก่อนเปิดให้จองรอบถัดไป<br>
            - หากมีข้อสงสัย กรุณาติดต่อเจ้าหน้าที่ผ่านหน้า “ติดต่อเรา”
        </div>
    </div>
    <?php include '../partials/user/footer_user.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>