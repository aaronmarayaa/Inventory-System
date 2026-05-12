<?php
    session_start();

    if (empty($_SESSION['loginSuccess']) || $_SESSION['loginSuccess'] !== true) {
        require_once __DIR__ . '/../security/rememberMe.php';
        require __DIR__ . '/../lib/connection.php';

        $rememberLoginSuccess = false;

        try {
            $rememberLoginSuccess = loginFromRememberMeCookie($conn);
        } catch (Throwable $exception) {
            error_log('Remember me session restore failed: ' . $exception->getMessage());
            clearRememberMeCookie();
        } finally {
            if (isset($conn)) {
                $conn->close();
            }
        }

        if (!$rememberLoginSuccess) {
            header("Location: ../exceptions/unauthorized.php");
            exit();
        }
    }

    session_regenerate_id(true);

    if((!isset($_SESSION["first_name"]) || empty($_SESSION["first_name"])) ||
        (!isset($_SESSION["last_name"]) || empty($_SESSION["last_name"])) ||
        (!isset($_SESSION["role"]) || empty($_SESSION["role"])) ||
        (!isset($_SESSION["id"]) || empty($_SESSION["id"]))
    ) {
        header("Location: ../exceptions/unauthorized.php");
        exit();
    }
?>
