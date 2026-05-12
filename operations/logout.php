<?php
session_start();

require_once __DIR__ . '/../security/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../exceptions/forbidden.php');
    exit();
}

requireValidCsrfToken('../pages/home.php?error=invalid_request');

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: ../index.php?logout=success');
exit;
?>
