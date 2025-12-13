<?php
$page_title = "Smazat uživatele";
require 'db_connect.php';
require "header.php";
require_admin();

$id = intval($_GET['id']);

$stmt = $db_connection->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

require "db_close.php";
header("Location: admin_users.php");
exit;
?>
