<?php
require 'db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$jmeno = trim($data['jmeno'] ?? '');
$prijmeni = trim($data['prijmeni'] ?? '');
$email = trim($data['email'] ?? '');
$heslo = trim($data['heslo'] ?? '');
$defaultRole = 'user';

if (!$jmeno || !$prijmeni || !$email || !$heslo) {
    echo json_encode(['success' => false, 'message' => 'Nevyplnili jste všechna pole']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Neplatný e-mail']);
    exit;
}

if (strlen($heslo) < 6) {
    echo json_encode(['success' => false, 'message' => 'Heslo musí mít alespoň 6 znaků']);
    exit;
}

// OPRAVA — $spojeni neexistuje
$stmt = $db_connection->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'E-mail je již registrován']);
    exit;
}
$stmt->close();

$hashHesla = password_hash($heslo, PASSWORD_DEFAULT);

// OPRAVA — použití správné proměnné
$stmt = $db_connection->prepare("INSERT INTO users (jmeno, prijmeni, email, heslo, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $jmeno, $prijmeni, $email, $hashHesla, $defaultRole);

if ($stmt->execute()) {

    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['user_jmeno'] = $jmeno;        // OPRAVA
    $_SESSION['user_prijmeni'] = $prijmeni;  // OPRAVA

    echo json_encode([
        'success' => true,
        'message' => 'Registrace proběhla úspěšně!',
        'user' => [
            'id' => $_SESSION['user_id'],
            'jmeno' => $_SESSION['user_jmeno'],
            'prijmeni' => $_SESSION['user_prijmeni']
        ]
    ]);

} else {
    echo json_encode(['success' => false, 'message' => 'Chyba při registraci']);
}

$stmt->close();
require 'db_close.php';
?>
