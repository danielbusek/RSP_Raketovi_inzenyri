<?php
require 'db_connect.php';
require 'header.php';
require_admin();

// 1) Získání ID z URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_users.php");
    exit;
}

$id = intval($_GET['id']);

// 2) Získat aktuální stav active
$stmt = $db_connection->prepare("SELECT active FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($active);
$stmt->fetch();
$stmt->close();

if ($active === null) { 
    // uživatel neexistuje
    header("Location: admin_users.php");
    exit;
}

// 3) Přepnout hodnotu
$newStatus = $active ? 0 : 1;

// 4) Uložit do DB
$stmt = $db_connection->prepare("UPDATE users SET active = ? WHERE id = ?");
$stmt->bind_param("ii", $newStatus, $id);
$stmt->execute();
$stmt->close();

require 'db_close.php';

// 5) Přesměrování
header("Location: admin_users.php");
exit;
?>
