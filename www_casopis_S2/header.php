<?php
require_once 'functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <title><?php page_title($GLOBALS['page_title'] ?? 'Raketoví inženýři'); ?></title>
</head>
<body>

<?php if ($GLOBALS['use_nav'] ?? true): ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-render sticky-top">
    <div class="container">
        
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <i class="fa-solid fa-rocket"></i> 
            RAKETOVÍ INŽENÝŘI
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="navMenu" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <li class="nav-item"><a class="nav-link" href="index.php">Přehled</a></li>
                <li class="nav-item"><a class="nav-link" href="#">O nás</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-user small"></i>
                            </div>
                            <span><?= htmlspecialchars($_SESSION['user_jmeno']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" style="background: #1c1e26;">
                            <li><a class="dropdown-item text-light" href="profile.php">Profil</a></li>
                            <?php if ($_SESSION['user_role'] === 'autor'): ?>
                                <li><a class="dropdown-item text-light" href="my_articles.php">Moje články</a></li>
                            <?php endif; ?>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <li><a class="dropdown-item text-warning" href="admin_users.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider bg-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Odhlásit se</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-sm btn-nav-login px-3 py-2 rounded-2" href="login.php">Log in</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-nav-signup px-3 py-2 rounded-2" href="register.php">Get Started</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>