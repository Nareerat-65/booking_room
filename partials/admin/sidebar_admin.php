<!-- ===== Sidebar ===== -->
<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="ad_dashboard.php" class="brand-link d-flex align-items-center gap-2">
            <img src="https://upload.wikimedia.org/wikipedia/th/b/b2/Medicine_Naresuan.png"
                alt="Logo" class="brand-image img-circle elevation-3"
                style="opacity:.9; width: 35px;height: 35px;">
            <span class="brand-text">ระบบจองห้องพัก</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" role="menu">
                <li class="nav-item">
                    <a href="/admin/ad_dashboard.php" class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?> ">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/admin/requests/ad_requests.php" class="nav-link <?= ($activeMenu ?? '') === 'requests' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-list"></i>
                        <p>รายการคำขอ</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/admin/calendar/ad_calendar.php" class="nav-link <?= ($activeMenu ?? '') === 'calendar' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>ปฏิทินห้องพัก</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/admin/documents/ad_doc_bookings.php" class="nav-link <?= ($activeMenu ?? '') === 'documents' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-text"></i>
                        <p>จัดการเอกสาร</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/admin/change_password/ad_change_password.php" class="nav-link <?= ($activeMenu ?? '') === 'change_password' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-key"></i>
                        <p>เปลี่ยนรหัสผ่าน</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/admin/ad_logout.php" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>ออกจากระบบ</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>