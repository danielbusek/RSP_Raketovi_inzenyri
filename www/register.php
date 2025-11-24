<?php
$page_title = "Registrace";
$stylesheet = "style.css";
$use_nav = false;
require "header.php";
?>

<div class="container">
    <h2>Registrace</h2>

    <form id="registerForm">
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

<script>
    document.getElementById("registerForm").addEventListener("submit", async function(e) {
        e.preventDefault();

        const jmeno = document.getElementById("jmeno").value;
        const prijmeni = document.getElementById("prijmeni").value;
        const email = document.getElementById("email").value;
        const heslo = document.getElementById("heslo").value;

        const response = await fetch('register_functions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ jmeno, prijmeni, email, heslo })
        });

        const data = await response.json();
        document.getElementById("message").innerText = data.message;

        if (data.success) {
            // Ukázat krátce informaci uživateli
            document.getElementById("message").innerText = "Registrace proběhla úspěšně!";

            // Redirect po 0.5s — působí to více user-friendly
            setTimeout(() => {
                window.location.href = "login.php";
            }, 500);
        }
    });
</script>

<?php require 'footer.php'; ?>
