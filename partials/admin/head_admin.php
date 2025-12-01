<!-- partials/head_admin.php -->
<meta charset="UTF-8">
<title><?= $pageTitle ?? 'แดชบอร์ดผู้ดูแล'; ?></title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-rc3/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css?family=Kanit&subset=thai,latin" rel="stylesheet">

<style>
    body {
        background: #fbf6f4;
        font-family: 'Kanit', sans-serif;
    }

    .app-header {
        background-color: #F57B39 !important;
        color: #fff !important;
        font-size: 20px;
    }

    .nav-link.active i {
        color: #fff !important;
    }
    

    .main-header .nav-link{
        color: #fff !important;
    }
</style>

<!-- เพิ่มเฉพาะหน้า (เช่น FullCalendar CSS ฯลฯ) -->
<?= $extraHead ?? '' ?>