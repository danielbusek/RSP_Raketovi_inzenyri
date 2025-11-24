<?php
// Funkce (title, stylesheet, role ochrana)
require 'functions.php';

// Start session pokud ještě neběží
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">

    <!-- Vlastní CSS -->
    <link rel="stylesheet" href="<?php stylesheet($GLOBALS['stylesheet'] ?? 'css/style.css'); ?>">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>

    <title><?php page_title($GLOBALS['page_title'] ?? 'Můj web'); ?></title>
</head>

<body>

<?php if ($GLOBALS['use_nav'] ?? true): ?>
<nav id="navbar" class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <!-- Logo -->
        <a class="navbar-brand" href="index.php">Časopis</a>

        <!-- Pravá část (desktop) -->
        <div class="d-flex align-items-center order-lg-3">

            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- Přihlášený uživatel -->
                <span class="navbar-text text-white me-3 d-none d-lg-inline">
                    Přihlášen:
                    <strong><?= htmlspecialchars($_SESSION['user_jmeno'] . " " . $_SESSION['user_prijmeni']) ?></strong>
                </span>

                <a class="btn btn-outline-danger btn-sm me-2 d-none d-lg-inline"
                   href="logout.php">
                    Odhlásit se
                </a>

            <?php else: ?>

                <!-- Nepřihlášený -->
                <a class="btn btn-outline-light btn-sm me-2 d-none d-lg-inline" href="login.php">
                    Přihlásit se
                </a>

                <a class="btn btn-success btn-sm d-none d-lg-inline" href="register.php">
                    Registrovat se
                </a>

            <?php endif; ?>

            <!-- Hamburger -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- MENU -->
        <div id="navMenu" class="collapse navbar-collapse order-lg-2">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">Domů</a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profil</a>
                    </li>

                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_users.php">Správa uživatelů</a>
                        </li>
                    <?php endif; ?>

                <?php endif; ?>

            </ul>

            <!-- Mobilní menu -->
            <ul class="navbar-nav d-lg-none">

                <?php if (isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <span class="nav-link">
                            Přihlášen:
                            <?= htmlspecialchars($_SESSION['user_jmeno'] . " " . $_SESSION['user_prijmeni']) ?>
                        </span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            Odhlásit se
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Přihlásit se</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Registrovat se</a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>
<?php endif; ?>
