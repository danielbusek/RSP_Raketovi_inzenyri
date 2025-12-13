<?php

function page_title($title = "Test funkčnosti") {
    echo htmlspecialchars($title);
}

function stylesheet($stylesheet = "style.css") {
    if (strpos($stylesheet, 'css/') === false) {
        echo htmlspecialchars("css/" . $stylesheet);
    } else {
        echo htmlspecialchars($stylesheet);
    }
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