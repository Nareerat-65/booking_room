<!-- partials/head_user.php -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'ระบบจองห้องพัก'; ?></title>

<link href="https://fonts.googleapis.com/css?family=Kanit&subset=thai,latin" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: #fbf6f4ff;
        font-family: 'Kanit', sans-serif;
    }

    .navbar {
        font-size: 0.95rem;
        backdrop-filter: blur(12px);
        background-color: #F57B39;
    }

    .navbar-brand {
        font-size: 1.9rem;
    }

    .nav-link {
        transition: 0.3s;
        font-size: 1.1rem;
    }

    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.15);
        border-radius: 0.5rem;
        padding-inline: 1rem;
    }
</style>

<!-- style / css เพิ่มเติมของแต่ละหน้า -->
<?= $extraHead ?? '' ?>
