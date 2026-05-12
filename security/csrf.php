<?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    function csrfToken(): string {
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            $token = bin2hex(random_bytes(16));
            $_SESSION['csrf_token'] = $token;
        } else {
            $token = $_SESSION['csrf_token'];
        }

        return $token;
    }

    function csrfInput(): string {
        $token = csrfToken();

        return '<input type="hidden" name="token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    function isCsrfTokenValid(): bool {
        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], (string) $_POST['token']);
    }

    function requireValidCsrfToken(string $redirectLocation = '../pages/home.php?error=invalid_request'): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isCsrfTokenValid()) {
            header('Location: ' . $redirectLocation);
            exit();
        }
    }
?>
