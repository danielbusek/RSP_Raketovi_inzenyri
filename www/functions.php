<?php

function page_title($title = "Test funkčnosti") {
    echo htmlspecialchars($title);
}

function stylesheet($stylesheet = "css/style.css") {
    echo htmlspecialchars("css/" . $stylesheet);
}

function require_admin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: index.php");
        exit;
    }
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>