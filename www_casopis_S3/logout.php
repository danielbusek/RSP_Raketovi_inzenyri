<?php

// Smazání všech proměnných v session
session_unset();

// Zničení session na serveru
session_destroy();

// Smazání session cookie (bezpečně)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Přesměrování na úvodní stránku
header("Location: index.php");
exit;
