<!-- partials/head_user.php -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'ระบบจองห้องพัก'; ?></title>

<!-- <link href="https://fonts.googleapis.com/css?family=Kanit&subset=thai,latin" rel="stylesheet"> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/gh/lazywasabi/thai-web-fonts@7/fonts/BaiJamjuree/BaiJamjuree.css" rel="stylesheet" />

<style>
    body {
        background: #fbf6f4ff;
        font-family: 'Bai Jamjuree', sans-serif;
    }

    .navbar {
        font-size: 0.95rem;
        backdrop-filter: blur(12px);
        background-color: #F57B39;
        position: sticky;
        top: 0;
        z-index: 1020;
    }

    .navbar-brand {
        font-size: 1.9rem;
    }

    .nav-link {
        transition: 0.3s;
        font-size: 1.1rem;
        background-color: #F57B39;
    }

    .nav-link:hover {
        background-color: white;
        color: #F57B39;
        border-radius: 0.5rem;
        padding-inline: 1rem;
    }

    .nav-link.active:hover {
        background-color: white !important;
        color: #F57B39 !important;
    }
</style>

<!-- style / css เพิ่มเติมของแต่ละหน้า -->
<?= $extraHead ?? '' ?>