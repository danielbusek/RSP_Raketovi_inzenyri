<?php
session_start();
require 'db_connect.php';

$email = trim($_POST['email'] ?? '');
$heslo = trim($_POST['heslo'] ?? '');

if (!$email || !$heslo) {
    header("Location: login.php?error=Nevyplnili jste všechna pole.");
    exit;
}

$stmt = $db_connection->prepare("
    SELECT users.id, users.jmeno, users.prijmeni, users.email, users.heslo, 
           users.active, role.role 
    FROM users 
    JOIN role ON users.id_role = role.id_role
    WHERE users.email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    header("Location: login.php?error=Neplatný e-mail nebo heslo.");
    exit;
}

$stmt->bind_result($id, $jmeno, $prijmeni, $emailDB, $hashHesla, $active, $role);
$stmt->fetch();

if (!$active) {
    header("Location: login.php?error=Účet byl deaktivován. Kontaktujte administrátora.");
    exit;
}

if (!password_verify($heslo, $hashHesla)) {
    header("Location: login.php?error=Neplatný e-mail nebo heslo.");
    exit;
}

$_SESSION['user_id'] = $id;
$_SESSION['user_jmeno'] = $jmeno;
$_SESSION['user_prijmeni'] = $prijmeni;
$_SESSION['user_email'] = $emailDB;
$_SESSION['user_role'] = strtolower($role);

header("Location: index.php?success=Vítejte!");
exit;
