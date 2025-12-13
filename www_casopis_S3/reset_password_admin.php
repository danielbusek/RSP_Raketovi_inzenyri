<?php
require 'db_connect.php';
require 'header.php';
require_admin(); // jen admin smí resetovat hesla

$id = intval($_GET['id']);
$user = $db_connection->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
?>

<div class="container mt-5" style="max-width: 500px;">
    <h2 class="text-center mb-4">Reset hesla</h2>

    <p><strong>Uživatel:</strong> <?= htmlspecialchars($user['jmeno'] . " " . $user['prijmeni']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nové heslo</label>
            <input type="password" name="heslo" class="form-control" required minlength="6">
        </div>

        <button class="btn btn-primary w-100">Uložit nové heslo</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $heslo = $_POST['heslo'];
        $hash = password_hash($heslo, PASSWORD_DEFAULT);

        $stmt = $db_connection->prepare("UPDATE users SET heslo=? WHERE id=?");
        $stmt->bind_param("si", $hash, $id);
        $stmt->execute();

        echo "<div class='alert alert-success mt-3'>Heslo bylo úspěšně změněno.</div>";
    }

    require 'footer.php';
    require 'db_close.php';
    ?>
</div>
