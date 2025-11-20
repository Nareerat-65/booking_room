<!-- partials/head_admin.php -->
<meta charset="UTF-8">
<title><?= $pageTitle ?? 'แดชบอร์ดผู้ดูแล'; ?></title>

<!-- CSS หลักของฝั่ง Admin -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link href="https://fonts.googleapis.com/css?family=Kanit&subset=thai,latin" rel="stylesheet" type="text/css" />

<style>
    body {
        background: #fbf6f4ff;
        font-family: 'Kanit', sans-serif;
    }

    /* เมนู active สีส้ม */
    .nav-sidebar .nav-link.active {
        background-color: #F57B39 !important;
        color: #fff !important;
    }

    .nav-sidebar .nav-link.active i {
        color: #fff !important;
    }

    .main-header {
        background-color: #F57B39 !important;
    }

    .main-header .nav-link,
    .main-header .navbar-brand,
    .main-header .navbar-text {
        color: #fff !important;
    }

    .brand-link {
        background-color: #111827;
    }

    .card-title {
        font-size: 1.4rem;
    }

    .card-header {
        background-color: #F57B39;
        opacity: 0.9;
    }

    .badge {
        font-size: 0.9rem;
    }
</style>

<!-- เพิ่มเฉพาะหน้า (เช่น FullCalendar CSS ฯลฯ) -->
<?= $extraHead ?? '' ?>