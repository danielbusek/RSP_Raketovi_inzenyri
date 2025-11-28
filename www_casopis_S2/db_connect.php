<?php
session_start();

//header('Content-Type: application/json');

//údaje k db
$db_host = 'localhost';
$db_name = 'rsp';
$db_user = 'root';
$db_pass = '';

//testuser@example.com
//user123

//admin@example.com
//admin123

//redaktor@example.com
//redaktor123

//zbytek jmen a hesel ve stejném formátu

//test připojení k db
$db_connection = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db_connection->connect_error) {
    die("Chyba: připojení k databázi se nezdařilo.");
}
