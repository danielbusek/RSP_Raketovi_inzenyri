<?php
$page_title = "Změna hesla";
require 'db_connect.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>

<div class="container mt-5" style="max-width: 500px;">
    <h2 class="text-center">Změna hesla</h2>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];

        // načtení hesla z DB
        $stmt = $db_connection->prepare("SELECT heslo FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($currentHash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($oldPassword, $currentHash)) {
            echo "<div class='alert alert-danger mt-3'>Původní heslo není správně.</div>";
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $db_connection->prepare("UPDATE users SET heslo=? WHERE id=?");
            $stmt->bind_param("si", $newHash, $user_id);
            $stmt->execute();

            echo "<div class='alert alert-success mt-3'>Heslo bylo úspěšně změněno.</div>";
        }
    }
    ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Původní heslo</label>
            <input type="password" name="old_password" required class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Nové heslo</label>
            <input type="password" name="new_password" required minlength="6" class="form-control">
        </div>

        <button class="btn btn-primary w-100">Změnit heslo</button>
    </form>
</div>

<?php
require 'footer.php';
require 'db_close.php';
?>
