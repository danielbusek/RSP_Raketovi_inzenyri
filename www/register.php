<?php
session_start();

header('Content-Type: application/json');

$host = 'localhost';
$db = 'myapp';
$user = 'root';
$pass = '';

$spojeni = new mysqli($host, $user, $pass, $db);
if ($spojeni->connect_error) {
    die(json_encode(['success' => false, 'message' => 'řipojení k databázi se nezdařilo']));
}

$data = json_decode(file_get_contents('php://input'), true);
$jmeno = trim($data['jmeno'] ?? '');
$email = trim($data['email'] ?? '');
$heslo = trim($data['heslo'] ?? '');

if (!$jmeno || !$email || !$heslo) {
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

$stmt = $spojeni->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'E-mail je již registrován']);
    exit;
}
$stmt->close();

$hashHesla = password_hash($heslo, PASSWORD_DEFAULT);

$stmt = $spojeni->prepare("INSERT INTO users (jmeno, email, heslo) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $jmeno, $email, $hashHesla);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'Registrace proběhla úspěšně!',
        'user' => [
            'id' => $_SESSION['user_id'],
            'jmeno' => $_SESSION['user_jmeno'],
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Chyba při registraci']);
}

$stmt->close();
$spojeni->close();
?>
