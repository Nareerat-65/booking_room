<!-- partials/head_admin.php -->
<meta charset="UTF-8">
<title><?= $pageTitle ?? 'แดชบอร์ดผู้ดูแล'; ?></title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-rc3/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/gh/lazywasabi/thai-web-fonts@7/fonts/BaiJamjuree/BaiJamjuree.css" rel="stylesheet" />
<link rel="stylesheet" href="/assets/css/admin/admin_global.css">

<!-- เพิ่มเฉพาะหน้า (เช่น FullCalendar CSS ฯลฯ) -->
<?= $extraHead ?? '' ?>