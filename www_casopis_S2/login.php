<?php
$page_title = "Přihlášení";
$stylesheet = "style.css";
$use_nav = false;
$use_foot = false;
require "header.php";
?>

<div class="container">
    <h2>Přihlášení</h2>

    <?php if (!empty($_GET['error'])): ?>
        <div class="message" style="color:red;"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['success'])): ?>
        <div class="message" style="color:green;"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <form action="login_functions.php" method="POST" id="loginForm">
        <div>
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="heslo">Heslo</label>
            <input type="password" id="heslo" name="heslo" required minlength="6">
        </div>

        <button type="submit">Přihlásit se</button>

        <div class="message" id="message"></div>
    </form>
</div>

<?php require 'footer.php'; ?>
