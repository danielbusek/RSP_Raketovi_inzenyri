<?php
$page_title = "Upravit uživatele";
require 'db_connect.php';
require "header.php";
require_admin();

// --- Nejprve zpracování POST! ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_GET['id']);
    $jmeno = $_POST['jmeno'];
    $prijmeni = $_POST['prijmeni'];
    $email = $_POST['email'];
    $id_role = intval($_POST['id_role']);

    $update = $db_connection->prepare("
        UPDATE users 
        SET jmeno=?, prijmeni=?, email=?, id_role=? 
        WHERE id=?
    ");
    $update->bind_param("sssii", $jmeno, $prijmeni, $email, $id_role, $id);
    $update->execute();

    header("Location: admin_users.php");
    exit;
}

// --- Načtení dat pro formulář ---
$id = intval($_GET['id']);

$user_stmt = $db_connection->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// role
$roles = $db_connection->query("SELECT id_role, role FROM role ORDER BY id_role ASC");
?>

<h2 class="text-center mt-4 mb-4">Upravit uživatele</h2>

<div class="container" style="max-width: 500px;">
    <form method="POST">

        <label>Jméno:</label>
        <input type="text" name="jmeno" value="<?= htmlspecialchars($user['jmeno']) ?>" required class="form-control mb-3">

        <label>Příjmení:</label>
        <input type="text" name="prijmeni" value="<?= htmlspecialchars($user['prijmeni']) ?>" required class="form-control mb-3">

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="form-control mb-3">

        <label>Role:</label>
        <select name="id_role" class="form-select mb-4">
            <?php while ($role = $roles->fetch_assoc()): ?>
                <option value="<?= $role['id_role'] ?>"
                    <?= ($user['id_role'] == $role['id_role']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($role['role']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn btn-primary w-100">Uložit</button>

    </form>
</div>

<?php
require "footer.php";
require "db_close.php";
?>
