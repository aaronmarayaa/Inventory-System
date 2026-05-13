<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function csrfToken(): string {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
            $token = bin2hex(random_bytes(16));
            $_SESSION['csrf_token'] = $token;
        } else {
            $token = $_SESSION['csrf_token'];
        }

        return $token;
    }

    function createCsrfToken(): string {
        return csrfToken();
    }

    function csrfInput(): string {
        $token = csrfToken();

        return '<input type="hidden" name="token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    function isCsrfTokenValid(): bool {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $_POST['token']);
    }

    function validateCsrfToken(string $redirectPath = '../index.php'): void {
        if (!isCsrfTokenValid()) {
            $_SESSION['error'] = 'Invalid request. Please refresh the page and try again.';
            header('Location: ' . $redirectPath);
            exit();
        }
    }

    function requireValidCsrfToken(string $redirectPath = '../index.php'): void {
        validateCsrfToken($redirectPath);
    }
?>
