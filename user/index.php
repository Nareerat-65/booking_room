<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ระบบจองห้องพัก</title>
    <link href="https://fonts.googleapis.com/css?family=Kanit&subset=thai,latin" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fbf6f4ff;
            font-family: 'Kanit', sans-serif;
        }

        .navbar {
            font-size: 0.95rem;
            backdrop-filter: blur(12px);
            background-color: #F57B39;
        }

        .navbar-brand {
            font-size: 1.9rem;
        }

        .nav-link {
            transition: 0.3s;
            font-size: 1.1rem;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 0.5rem;
            padding-inline: 1rem;
        }

        .btn-main {
            background-color: #F57B39;
            border: 0;
            border-radius: 999px;
            padding: .6rem 1.8rem;
            color: #fff;
        }

        .btn-main:hover {
            opacity: .9;
            color: #fff;
        }

        .hero {
            padding: 4rem 0 3rem;
            text-align: center;
        }

        .step-card {
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
            border: 0;
        }
    </style>
</head>

<body>
    <!-- navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold py-2 d-flex align-items-center" href="#">
                <img src="../img/Medicine_Naresuan.png" alt="Logo" width="80" height="80" class="me-3">

                <span style="line-height:1; font-size:1.8rem;">
                    ระบบจองห้องพัก
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="index.php">หน้าแรก</a></li>
                    <li class="nav-item"><a class="nav-link" href="u_booking.php">จองห้องพัก</a></li>
                    <li class="nav-item"><a class="nav-link" href="u_contact.php">ติดต่อเรา</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <div class="container hero">
        <h1 class="mb-3">ระบบจองห้องพักสำหรับนิสิตแพทย์ / แพทย์ / บุคลากร</h1>
        <p class="text-muted mb-4">
            กรอกคำขอจองออนไลน์ รอการอนุมัติผ่านอีเมล และรับลิงก์สำหรับกรอกรายชื่อผู้เข้าพักในแต่ละห้อง
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="u_booking.php" class="btn btn-main">เริ่มจองห้องพัก</a>
            <a href="#steps" class="btn btn-outline-secondary rounded-pill">ดูขั้นตอนการจอง</a>
        </div>
    </div>

    <!-- ขั้นตอนการจอง -->
    <div id="steps" class="container pb-4">
        <h2 class="h4 mb-3">ขั้นตอนการใช้งาน</h2>
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
        <div class="alert alert-warning">
            <strong>ประกาศ / เงื่อนไขการเข้าพัก</strong><br>
            - กรุณาส่งคำขอจองล่วงหน้าอย่างน้อย 7 วันก่อนวันที่เข้าพัก<br>
            - ห้องพัก 1 ห้องเข้าพักได้สูงสุด 4 คน แยกห้องตามเพศ (ชาย/หญิง)<br>
            - หลังย้ายออกจะมีการทำความสะอาดห้อง 3 วัน ก่อนเปิดให้จองรอบถัดไป<br>
            - หากมีข้อสงสัย กรุณาติดต่อเจ้าหน้าที่ผ่านหน้า “ติดต่อเรา”
        </div>
    </div>

    <footer class="py-3 border-top bg-white text-center">
        <div class="container small text-muted">
            <div class="mb-2">
                หน่วยงานกิจการนิสิต คณะแพทยศาสตร์ มหาวิทยาลัยนเรศวร
            </div>
            <div class="mb-2">
                เลขที่ 99 หมู่ 9 ตำบลท่าโพธิ์ อำเภอเมืองพิษณุโลก จังหวัดพิษณุโลก รหัสไปรษณีย์ 65000
            </div>
            <div class="mb-2">
                โทร 0-5596-7847 | Email: example@example.com
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>