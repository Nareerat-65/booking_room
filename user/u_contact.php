<?php
require_once dirname(__DIR__, 1) . '/config.php';
$pageTitle = 'ติดต่อเรา - ระบบจองห้องพัก';
$extraHead = '<link rel="stylesheet" href="/assets/css/user/u_contact.css">';
$activeMenu = 'contact';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <?php include_once PARTIALS_PATH . '/user/head_user.php'; ?>
</head>

<body>
    <?php include_once PARTIALS_PATH . '/user/nav_user.php'; ?>
    <div class="container py-5">
        <h2 class="text-center mb-5">ติดต่อเรา</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-3 ">
                <div class="contact-box text-center p-4  bg-white shadow-sm">
                    <i class="fas fa-map-marker-alt icon-lg mb-3"></i>
                    <h5 class="mt-3 fw-bold">ที่ตั้ง</h5>
                    <p class="text-muted small">
                        เลขที่ 99 หมู่ 9 ถนนพิษณุโลก–นครสวรรค์<br>
                        ตำบลท่าโพธิ์ อำเภอเมืองพิษณุโลก<br>
                        จังหวัดพิษณุโลก 65000
                    </p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="contact-box text-center p-4 bg-white shadow-sm">
                    <i class="fas fa-phone icon-lg mb-3"></i>
                    <h5 class="mt-3 fw-bold">โทรศัพท์</h5>
                    <p class="text-muted small">
                        082-7946535
                    </p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="contact-box text-center p-4 bg-white shadow-sm">
                    <i class="fas fa-envelope icon-lg mb-3"></i>
                    <h5 class="mt-3 fw-bold">E-Mail</h5>
                    <p class="text-muted small">
                        dormitory@nu.ac.th
                    </p>
                </div>
            </div>

                <div class="col-md-3">
                    <a href="https://www.facebook.com/SACMEDNU" class="contact-link">
                        <div class="contact-box text-center p-4 bg-white shadow-sm">
                            <i class="fas fa-comments icon-lg mb-3"></i>
                            <h5 class="mt-3 fw-bold">Q&A</h5>
                            <p class="text-muted small">Messenger</p>
                        </div>
                    </a>
                </div>
        </div>

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


    <?php include_once PARTIALS_PATH . '/user/footer_user.php'; ?>
    <?php include_once PARTIALS_PATH . '/user/script_user.php'; ?>
</body>

</html>