<?php
$page_title = "Přihlášení";
$stylesheet = "style.css";
require "header.php";
?>

<div class="container">
    <h2>Přihlášení</h2>

    <form id="loginForm">
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

<script>
    document.getElementById("loginForm").addEventListener("submit", async function(e) {
        e.preventDefault();

        const email = document.getElementById("email").value;
        const heslo = document.getElementById("heslo").value;

        const response = await fetch('login_functions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, heslo })
        });

        const data = await response.json();
        document.getElementById("message").innerText = data.message;

        if (data.success) {
            this.reset();
        }
    });
</script>

<?php require 'footer.php'; ?>
