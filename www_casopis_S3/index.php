<?php
$page_title = "Řídící centrum";
$stylesheet = "index.css";
require 'db_connect.php';
require 'header.php';

$logged = isset($_SESSION['user_id']);
$jmeno = $logged ? htmlspecialchars($_SESSION['user_jmeno']) : "";
?>

<div class="hero-section">
    <div class="container">
        <h1 class="display-3 mb-3 fw-bold">VĚDA & VESMÍR</h1>
        <p class="lead text-muted mb-4">
            Procházejte databázi recenzovaných článků nebo publikujte svůj vlastní výzkum.
        </p>
        <?php if (!$logged): ?>
            <a href="register.php" class="btn btn-primary btn-lg px-5 rounded-pill">GET STARTED</a>
        <?php else: ?>
            <h4 class="text-white">Vítej zpět, agente <?= $jmeno ?> 👋</h4>
        <?php endif; ?>
    </div>
</div>

<div class="container mb-5">

    <h4 class="section-title">Mix kategorií</h4>

    <div class="scroll-row">
        <?php for ($i = 0; $i < 10; $i++): ?>
            <div class="article-card">
                <div class="img-placeholder"></div>
                <div class="body">
                    <strong>Title <?= $i+1 ?></strong><br>
                    Kategorie: <em>Mix</em><br>
                    <span class="small text-muted">Krátký popis článku...</span>
                </div>
            </div>
        <?php endfor; ?>
    </div>


    <h4 class="section-title">Zábavná kategorie</h4>

    <div class="scroll-row">
        <?php for ($i = 0; $i < 10; $i++): ?>
            <div class="article-card">
                <div class="img-placeholder"></div>
                <div class="body">
                    <strong>Title <?= $i+1 ?></strong><br>
                    Kategorie: <em>Zábava</em><br>
                    <span class="small text-muted">Vesmírné vtipy a zajímavosti...</span>
                </div>
            </div>
        <?php endfor; ?>
    </div>

</div>

<script>
document.querySelectorAll('.scroll-row').forEach((row) => {
    row.addEventListener('wheel', (e) => {
        if (e.deltaY !== 0) {
            e.preventDefault();
            row.scrollLeft += e.deltaY;
        }
    });
});

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
    row.addEventListener('mouseleave', () => { isDown = false; row.classList.remove('active'); });
    row.addEventListener('mouseup', () => { isDown = false; row.classList.remove('active'); });
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