<?php
$page_title = "Registrace";
$stylesheet = "style.css";
$use_nav = false;
$use_foot = false;
require "header.php";
?>

<div class="container">
    <h2>Registrace</h2>

    <?php if (!empty($_GET['error'])): ?>
        <div class="message" style="color:red;"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form action="register_functions.php" method="POST" id="registerForm">
        <div>
            <label for="jmeno">Jméno</label>
            <input type="text" id="jmeno" name="jmeno" required>
        </div>

        <div>
            <label for="prijmeni">Příjmení</label>
            <input type="text" id="prijmeni" name="prijmeni" required>
        </div>

        <div>
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="heslo">Heslo</label>
            <input type="password" id="heslo" name="heslo" required minlength="6">
        </div>

        <button type="submit">Registrovat se</button>

        <div class="message" id="message"></div>
    </form>
</div>

<?php require 'footer.php'; ?>
