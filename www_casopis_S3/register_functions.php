<?php
session_start();
require 'db_connect.php';

$jmeno = trim($_POST['jmeno'] ?? '');
$prijmeni = trim($_POST['prijmeni'] ?? '');
$email = trim($_POST['email'] ?? '');
$heslo = trim($_POST['heslo'] ?? '');
$defaultRole = 7; // například Čtenář

if (!$jmeno || !$prijmeni || !$email || !$heslo) {
    header("Location: register.php?error=Nevyplnili jste všechna pole.");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=Neplatný e-mail.");
    exit;
}

if (strlen($heslo) < 6) {
    header("Location: register.php?error=Heslo musí mít alespoň 6 znaků.");
    exit;
}

$stmt = $db_connection->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: register.php?error=E-mail je již registrován.");
    exit;
}

$stmt->close();

$hash = password_hash($heslo, PASSWORD_DEFAULT);

$stmt = $db_connection->prepare("
    INSERT INTO users (jmeno, prijmeni, email, heslo, id_role, active) 
    VALUES (?, ?, ?, ?, ?, 1)
");
$stmt->bind_param("ssssi", $jmeno, $prijmeni, $email, $hash, $defaultRole);
$stmt->execute();

header("Location: login.php?success=Registrace proběhla úspěšně – nyní se můžete přihlásit.");
exit;
