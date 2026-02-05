<?php
require_once dirname(__DIR__, 1) . '/config.php';
$pageTitle = 'ระบบจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="../assets/css/user/index.css">';
$activeMenu = 'index';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <?php include_once PARTIALS_PATH . '/user/head_user.php'; ?>
</head>

<body>
    <?php include_once PARTIALS_PATH . '/user/nav_user.php'; ?>
    <div class="container-fluid hero px-0">
        <h1 class="mb-3">ระบบจองห้องพักสำหรับนิสิตแพทย์ / แพทย์ / บุคลากร</h1>
        <p class=" text-white mb-4">
            กรอกคำขอจองออนไลน์และรอรับผลการอนุมัติผ่านอีเมล
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="u_booking.php" class="btn btn-main">เริ่มจองห้องพัก</a>
            <a href="#steps" class="btn btn-outline-light rounded-pill">ดูขั้นตอนการจอง</a>
        </div>
    </div>

    <div id="steps" class="container pb-4 mt-4 ">
        <h2 class="h4 mb-3 text-center mb-4">ขั้นตอนการจอง</h2>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card step-card h-100 p-3">
                    <h5>1. ส่งคำขอการจอง</h5>
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
                        ได้รับอีเมลแจ้งผลการอนุมัติคำขอการจอง
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
    

    <div class="container pb-5">
        <div class="alert alert-warning">
            <strong class="d-block mb-2">ประกาศ / เงื่อนไขการเข้าพัก</strong>

            <ul class="mb-0 ps-3">
                <li>
                    กรุณาส่งคำขอจองล่วงหน้าอย่างน้อย
                    <strong>2 สัปดาห์ก่อนวันที่เข้าพัก</strong>
                    <br>
                    <small class="text-muted">
                        กรณีต้องการจองในระยะเวลาน้อยกว่า 2 สัปดาห์ กรุณาติดต่อเจ้าหน้าที่โดยตรง
                    </small>
                </li>
                <li>
                    ห้องพัก 1 ห้องเข้าพักได้สูงสุด 4 คน
                    และแยกห้องตามเพศ (ชาย / หญิง)
                </li>
                <li>
                    หลังย้ายออกจะมีการทำความสะอาดห้อง 3 วัน
                    ก่อนเปิดให้จองรอบถัดไป
                </li>
                <li>
                    หากมีข้อสงสัย กรุณาติดต่อเจ้าหน้าที่ผ่านหน้า
                    <strong>“ติดต่อเรา”</strong>
                </li>
            </ul>
        </div>
    </div>

   

    <?php include_once PARTIALS_PATH . '/user/footer_user.php'; ?>
    <?php include_once PARTIALS_PATH . '/user/script_user.php'; ?>
</body>

</html>