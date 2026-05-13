<?php
    session_start();

    require_once __DIR__ . '/../security/csrf.php';
    requireValidCsrfToken('../index.php?error=invalid_request');

    $rememberMePath = __DIR__ . '/../security/rememberMe.php';
    $connectionPath = __DIR__ . '/../lib/connection.php';

    if (file_exists($rememberMePath) && file_exists($connectionPath)) {
        require_once $rememberMePath;
        require $connectionPath;

        try {
            if (function_exists('deleteRememberMeToken')) {
                deleteRememberMeToken($conn);
            }
        } catch (Throwable $exception) {
            error_log('Remember me logout cleanup failed: ' . $exception->getMessage());
        } finally {
            if (isset($conn)) {
                $conn->close();
            }
        }

        if (function_exists('clearRememberMeCookie')) {
            clearRememberMeCookie();
        }
    }

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
    exit();
?>
