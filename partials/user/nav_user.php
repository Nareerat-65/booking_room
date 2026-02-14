<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold py-2 d-flex align-items-center" href="#">
            <img src="../../assets/img/Medicine_Naresuan.png" alt="Logo" width="70" height="70" class="me-3">

            <span style="line-height:1; font-size:1.8rem;">
                ระบบจองห้องพัก
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav gap-2">
                <li class="nav-item"><a class="nav-link <?= ($activeMenu ?? '')==='index' ? 'active' : '' ?>" href="index.php">หน้าแรก</a></li>
                <li class="nav-item"><a class="nav-link <?= ($activeMenu ?? '')==='booking' ? 'active' : '' ?>" href="u_booking.php">จองห้องพัก</a></li>
                <li class="nav-item"><a class="nav-link <?= ($activeMenu ?? '')==='contact' ? 'active' : '' ?>" href="u_contact.php">ติดต่อเรา</a></li>
            </ul>
        </div>
    </div>
</nav>