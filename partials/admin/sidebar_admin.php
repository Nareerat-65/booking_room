<!-- ===== Sidebar ===== -->
<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="ad_dashboard.php" class="brand-link d-flex align-items-center gap-2">
            <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png"
                alt="Logo" class="brand-image img-circle elevation-3"
                style="opacity:.9; width:34px;height:34px;">
            <span class="brand-text">ระบบจองห้องพัก</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center gap-2 px-3">
            <i class="fas fa-user-circle fa-2x text-white"></i>
            <div class="info">
                <span class="d-block text-white">
                    <?= htmlspecialchars($_SESSION['admin_name']) ?>
                </span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" role="menu">
                <li class="nav-item">
                    <a href="ad_dashboard.php" class="nav-link <?= ($activeMenu ?? '')==='dashboard' ? 'active' : '' ?> ">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="ad_requests.php" class="nav-link <?= ($activeMenu ?? '')==='requests' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-list"></i>
                        <p>รายการคำขอจองห้องพัก</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="ad_calendar.php" class="nav-link <?= ($activeMenu ?? '')==='calendar' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>ปฏิทินห้องพัก</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="ad_change_password.php" class="nav-link <?= ($activeMenu ?? '')==='change_password' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-key"></i>
                        <p>เปลี่ยนรหัสผ่าน</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="ad_logout.php" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>ออกจากระบบ</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>