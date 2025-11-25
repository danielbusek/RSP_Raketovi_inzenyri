<?php
$page_title = "Domovská stránka";
$stylesheet = "index.css";
require 'db_connect.php';
require 'header.php';

// Kontrola přihlášení
$logged = isset($_SESSION['user_id']);
$jmeno = $logged ? htmlspecialchars($_SESSION['user_jmeno']) : "";
?>


<div class="container mt-4 mb-5">

    <!-- Uvítání uživatele -->
    <?php if ($logged): ?>
        <h3 class="mb-4">Vítej, <?= $jmeno ?> 👋</h3>
    <?php endif; ?>

    <!-- Sekce 1 – Mix kategorií -->
    <h4 class="section-title">Mix kategorií</h4>

    <div class="scroll-row">
        <?php for ($i = 0; $i < 10; $i++): ?>
            <div class="article-card">
                <div class="img-placeholder"></div>
                <div class="body">
                    <strong>Title <?= $i+1 ?></strong><br>
                    Kategorie: <em>Mix</em><br>
                    Popis článku...
                </div>
            </div>
        <?php endfor; ?>
    </div>


    <!-- Sekce 2 – Zábavná kategorie -->
    <h4 class="section-title">Zábavná kategorie</h4>

    <div class="scroll-row">
        <?php for ($i = 0; $i < 10; $i++): ?>
            <div class="article-card">
                <div class="img-placeholder"></div>
                <div class="body">
                    <strong>Title <?= $i+1 ?></strong><br>
                    Kategorie: <em>Mix</em><br>
                    Popis článku...
                </div>
            </div>
        <?php endfor; ?>
    </div>


</div>

<script>
// Horizontal scroll with mouse wheel
document.querySelectorAll('.scroll-row').forEach((row) => {
    row.addEventListener('wheel', (e) => {
        if (e.deltaY !== 0) {
            e.preventDefault();
            row.scrollLeft += e.deltaY;
        }
    });
});

// Drag scrolling
document.querySelectorAll('.scroll-row').forEach((row) => {
    let isDown = false;
    let startX;
    let scrollLeft;

    row.addEventListener('mousedown', (e) => {
        isDown = true;
        row.classList.add('active');
        startX = e.pageX - row.offsetLeft;
        scrollLeft = row.scrollLeft;
    });

    row.addEventListener('mouseleave', () => {
        isDown = false;
        row.classList.remove('active');
    });

    row.addEventListener('mouseup', () => {
        isDown = false;
        row.classList.remove('active');
    });

    row.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - row.offsetLeft;
        const walk = (x - startX);
        row.scrollLeft = scrollLeft - walk;
    });
});
</script>




<?php
require "footer.php";
require "db_close.php";
?>
