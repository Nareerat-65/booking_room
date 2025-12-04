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
                <div class="user-panel mt-1 d-flex align-items-center gap-2 px-3">
                    <i class="fas fa-user-circle fa-2x text-white"></i>
                    <div class="info">
                        <span class="d-block text-white fs-5">
                            <?= htmlspecialchars($_SESSION['admin_name']) ?>
                        </span>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>