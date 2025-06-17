<?php
session_start();

function check_login() {
    return isset($_SESSION['user']);
}

function require_login() {
    if (!check_login()) {
        header("Location: login.php");
        exit();
    }
}

function is_admin() {
    return $_SESSION['is_admin'] ?? false;
}

function require_admin() {
    if (!is_admin()) {
        http_response_code(403);
        exit("Forbidden: Admins only.");
    }
}
?>
