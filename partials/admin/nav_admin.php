<!-- ===== Navbar ===== -->
<nav class="app-header navbar navbar-expand ">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <!-- AdminLTE4 toggle -->
                <a class="nav-link text-white" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <span class="nav-link fw-bold text-white">
                    <?= htmlspecialchars($pageTitle ?? 'แดชบอร์ดผู้ดูแล') ?>
                    </span>

            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a href="ad_logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                </a>
            </li>
        </ul>
    </div>
</nav>