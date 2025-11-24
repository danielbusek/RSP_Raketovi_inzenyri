<?php
$page_title = "Přidat uživatele";
require 'db_connect.php';
require "header.php";
require_admin();

// Nejprve POST zpracování
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jmeno = trim($_POST['jmeno']);
    $prijmeni = trim($_POST['prijmeni']);
    $email = trim($_POST['email']);
    $heslo = trim($_POST['heslo']);
    $id_role = intval($_POST['id_role']);

    // Kontrola prázdných polí
    if (!$jmeno || !$prijmeni || !$email || !$heslo) {
        $error = "Vyplňte všechna pole.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Neplatný e-mail.";
    } 
    elseif (strlen($heslo) < 6) {
        $error = "Heslo musí mít alespoň 6 znaků.";
    } 
    else {
        // Kontrola, zda email už existuje
        $check = $db_connection->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Uživatel s tímto e-mailem již existuje.";
        } else {
            // Hash hesla
            $hashHesla = password_hash($heslo, PASSWORD_DEFAULT);

            // Uložení nového uživatele
            $stmt = $db_connection->prepare("
                INSERT INTO users (jmeno, prijmeni, email, heslo, id_role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssi", $jmeno, $prijmeni, $email, $hashHesla, $id_role);
            $stmt->execute();

            header("Location: admin_users.php");
            exit;
        }
    }
}

// Načtení rolí z tabulky role
$roles = $db_connection->query("SELECT id_role, role FROM role ORDER BY id_role");
?>

<div class="container" style="max-width: 500px;">

    <h2 class="text-center mt-4 mb-4">Přidat uživatele</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>Jméno:</label>
        <input type="text" name="jmeno" required class="form-control mb-3">

        <label>Příjmení:</label>
        <input type="text" name="prijmeni" required class="form-control mb-3">

        <label>Email:</label>
        <input type="email" name="email" required class="form-control mb-3">

        <label>Heslo:</label>
        <input type="password" name="heslo" minlength="6" required class="form-control mb-3">

        <label>Role:</label>
        <select name="id_role" class="form-select mb-4" required>
            <?php while ($role = $roles->fetch_assoc()): ?>
                <option value="<?= $role['id_role'] ?>">
                    <?= htmlspecialchars($role['role']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn btn-primary w-100">Přidat uživatele</button>

    </form>
</div>

<?php
require "footer.php";
require "db_close.php";
?>
    