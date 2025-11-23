<?php
require 'db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$heslo = trim($data['heslo'] ?? '');

if (!$email || !$heslo) {
    echo json_encode(['success' => false, 'message' => 'Nevyplnili jste všechna pole.']);
    exit;
}

$stmt = $db_connection->prepare("SELECT id, heslo FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Neplatný e-mail nebo heslo']);
    exit;
}

$stmt->bind_result($id, $hashHesla);
$stmt->fetch();

if (password_verify($heslo, $hashHesla)) {
    $_SESSION['user_id'] = $id;

    echo json_encode(['success' => true, 'message' => 'Přihlášení proběhlo úspěšně!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Neplatný e-mail nebo heslo']);
}

$stmt->close();
require 'db_close.php';
?>
