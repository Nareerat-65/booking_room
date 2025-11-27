<?php
$pageTitle = 'ติดต่อเรา - ระบบจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="/assets/css/user/u_contact.css">';
$activeMenu = 'contact';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php include '../partials/user/head_user.php'; ?>
</head>

<body>
    <!-- navbar -->
    <?php include '../partials/user/nav_user.php'; ?>

    <!-- main content -->
    <div class="container py-5">
        <h2 class="text-center mb-5">ติดต่อเรา</h2>
        <div class="row g-4 justify-content-center">
            <!-- การ์ด 1 -->
            <div class="col-md-3 ">
                <div class="contact-box text-center p-4 bg-white shadow-sm">
                    <i class="bi bi-geo-alt icon-lg"></i>
                    <h5 class="mt-3 fw-bold">ที่ตั้ง</h5>
                    <p class="text-muted small">
                        เลขที่ 99 หมู่ 9 ถนนพิษณุโลก–นครสวรรค์<br>
                        ตำบลท่าโพธิ์ อำเภอเมืองพิษณุโลก<br>
                        จังหวัดพิษณุโลก 65000
                    </p>
                </div>
            </div>

            <!-- การ์ด 2 -->
            <div class="col-md-3">
                <div class="contact-box text-center p-4 bg-white shadow-sm">
                    <i class="bi bi-telephone icon-lg"></i>
                    <h5 class="mt-3 fw-bold">โทรศัพท์</h5>
                    <p class="text-muted small">
                        0-5596-7847
                    </p>
                </div>
            </div>

            <!-- การ์ด 3 -->
            <div class="col-md-3">
                <div class="contact-box text-center p-4 bg-white shadow-sm">
                    <i class="bi bi-envelope icon-lg"></i>
                    <h5 class="mt-3 fw-bold">E-Mail</h5>
                    <p class="text-muted small">
                        dormitory@nu.ac.th
                    </p>
                </div>
            </div>

            <!-- การ์ด 4 -->
            <div class="col-md-3">
                <div class="contact-box text-center p-4 bg-white shadow-sm">
                    <i class="bi bi-chat-dots icon-lg"></i>
                    <h5 class="mt-3 fw-bold">Q&A</h5>
                    <p class="text-muted small">Messenger</p>
                </div>
            </div>
        </div>

        <!-- แผนที่ -->
        <div class="row mt-5">
            <div class="col-md-10 mx-auto">
                <div class="map-box p-4 bg-white shadow-sm">
                    <h5 class="fw-bold mb-3">แผนที่ตั้ง</h5>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3820.5597672934327!2d100.18645437495645!3d16.748803620887543!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30dfbea1490380a5%3A0x1d7087a8039d6b1!2z4LmC4Lij4LiH4Lie4Lii4Liy4Lia4Liy4Lil4Lih4Lir4Liy4Lin4Li04LiX4Lii4Liy4Lil4Lix4Lii4LiZ4LmA4Lij4Lio4Lin4Lij!5e0!3m2!1sth!2sth!4v1763451614018!5m2!1sth!2sth"
                        width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- footer -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>