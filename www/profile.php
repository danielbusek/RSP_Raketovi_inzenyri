<?php
$page_title = "Můj profil";
require 'db_connect.php';
require 'header.php';

// Uživatel musí být přihlášen
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$successMsg = "";
$errorMsg = "";

// --------------------------------------------------------
// 1) ZPRACOVÁNÍ FORMULÁŘE – změna jména, příjmení a emailu
// --------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $jmeno = trim($_POST['jmeno'] ?? '');
    $prijmeni = trim($_POST['prijmeni'] ?? '');
    $email_new = trim($_POST['email'] ?? '');

    if (!$jmeno || !$prijmeni || !$email_new) {
        $errorMsg = "Vyplňte prosím všechna pole.";
    } elseif (!filter_var($email_new, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Zadejte platný e-mail.";
    } else {
        // Kontrola, zda email nepoužívá někdo jiný
        $check = $db_connection->prepare("
            SELECT id FROM users 
            WHERE email = ? AND id <> ?
        ");
        $check->bind_param("si", $email_new, $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errorMsg = "Tento e-mail už používá jiný uživatel.";
        } else {
            // Update uživatele
            $stmt = $db_connection->prepare("
                UPDATE users 
                SET jmeno = ?, prijmeni = ?, email = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssi", $jmeno, $prijmeni, $email_new, $user_id);
            $stmt->execute();
            $stmt->close();

            // Aktualizace session
            $_SESSION['user_jmeno'] = $jmeno;
            $_SESSION['user_prijmeni'] = $prijmeni;
            $_SESSION['user_email'] = $email_new;

            $successMsg = "Údaje byly úspěšně aktualizovány.";
        }

        $check->close();
    }
}

// --------------------------------------------------------
// 2) NAČTENÍ AKTUÁLNÍCH ÚDAJŮ
// --------------------------------------------------------
$stmt = $db_connection->prepare("
    SELECT users.jmeno, users.prijmeni, users.email, role.role 
    FROM users 
    JOIN role ON users.id_role = role.id_role 
    WHERE users.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($jmeno, $prijmeni, $email, $role);
$stmt->fetch();
$stmt->close();
?>

<div class="container mt-5" style="max-width: 600px;">

    <h2 class="text-center mb-4">Můj profil</h2>

    <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">

        <div class="mb-3">
            <label class="form-label"><strong>Jméno</strong></label>
            <input type="text" name="jmeno" class="form-control"
                   value="<?= htmlspecialchars($jmeno) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Příjmení</strong></label>
            <input type="text" name="prijmeni" class="form-control"
                   value="<?= htmlspecialchars($prijmeni) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>E-mail</strong></label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Role (nelze změnit)</strong></label>
            <input type="text" class="form-control"
                   value="<?= htmlspecialchars($role) ?>" disabled>
        </div>

        <button class="btn btn-primary w-100">Uložit změny</button>
    </form>

    <a href="change_password.php" class="btn btn-warning w-100">
        Změnit heslo
    </a>

</div>

<?php
require 'footer.php';
require 'db_close.php';
?>
