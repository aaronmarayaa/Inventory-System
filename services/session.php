<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['loginSuccess']) || $_SESSION['loginSuccess'] !== true) {
        require_once __DIR__ . '/../security/rememberMe.php';
        require __DIR__ . '/../lib/connection.php';

        loginFromRememberMeCookie($conn);

        if (isset($conn)) {
            $conn->close();
        }
    }

    if (empty($_SESSION['loginSuccess']) || $_SESSION['loginSuccess'] !== true) {
        header('Location: ../exceptions/unauthorized.php');
        exit();
    }

    if (
        (!isset($_SESSION['first_name']) || empty($_SESSION['first_name'])) ||
        (!isset($_SESSION['last_name']) || empty($_SESSION['last_name'])) ||
        (!isset($_SESSION['role']) || empty($_SESSION['role'])) ||
        (!isset($_SESSION['id']) || empty($_SESSION['id']))
    ) {
        header('Location: ../exceptions/unauthorized.php');
        exit();
    }
?>
