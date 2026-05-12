<?php
    session_start();

    require_once __DIR__ . '/../security/rememberMe.php';
    require __DIR__ . '/../lib/connection.php';

    try {
        deleteRememberMeToken($conn);
    } catch (Throwable $exception) {
        error_log('Remember me logout cleanup failed: ' . $exception->getMessage());
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }

    clearRememberMeCookie();

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
