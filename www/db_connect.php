<?php
session_start();

//testuser@example.com
//testuser

//admin@example.com
//admin123

//údaje k db
$db_host = '';
$db_name = '';
$db_user = '';
$db_pass = '';

//test připojení k db
$db_connection = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($db_connection->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Připojení k databázi se nezdařilo']));
}
?>