<?php
$page_title = "Domů";
$stylesheet = "";
require "header.php";
?>

<h1>Test přesměrování na index</h1>

<?php if (!empty($_SESSION)): ?>
    <h3>Session obsah:</h3>
    <pre><?php print_r($_SESSION); ?></pre>
<?php else: ?>
    <p>Uživatel není přihlášen.</p>
<?php endif; ?>

<?php require "footer.php"; ?>
