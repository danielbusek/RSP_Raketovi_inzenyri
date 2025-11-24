<?php
require 'db_connect.php';
header('Content-Type: application/json');

// Zahájit session pokud ještě neběží
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Načtení JSON
$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$heslo = trim($data['heslo'] ?? '');

// Validace vstupu
if (!$email || !$heslo) {
    echo json_encode(['success' => false, 'message' => 'Nevyplnili jste všechna pole.']);
    exit;
}

// SQL dotaz – nyní zahrnuje ACTIVE
$stmt = $db_connection->prepare("
    SELECT users.id, users.jmeno, users.prijmeni, users.email, users.heslo, users.active,
           role.role
    FROM users
    JOIN role ON users.id_role = role.id_role
    WHERE users.email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

// Není uživatel?
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Neplatný e-mail nebo heslo']);
    exit;
}

$stmt->bind_result($id, $jmeno, $prijmeni, $emailDB, $hashHesla, $active, $role);
$stmt->fetch();

// 🔒 Zkontroluj, jestli je účet aktivní
if ((int)$active === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Váš účet byl deaktivován. Kontaktujte administrátora.'
    ]);
    exit;
}

// Zkontrolujeme heslo
if (!password_verify($heslo, $hashHesla)) {
    echo json_encode(['success' => false, 'message' => 'Neplatný e-mail nebo heslo']);
    exit;
}

// ✔ Uložení do session
$_SESSION['user_id'] = $id;
$_SESSION['user_jmeno'] = $jmeno;
$_SESSION['user_prijmeni'] = $prijmeni;
$_SESSION['user_email'] = $emailDB;
$_SESSION['user_role'] = mb_strtolower($role, 'UTF-8');

// Odpověď
echo json_encode([
    'success' => true,
    'message' => 'Přihlášení proběhlo úspěšně!',
    'user' => [
        'id' => $id,
        'jmeno' => $jmeno,
        'prijmeni' => $prijmeni,
        'email' => $emailDB,
        'role' => $role
    ]
]);

$stmt->close();
require 'db_close.php';
?>
