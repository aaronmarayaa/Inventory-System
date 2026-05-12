<?php
require_once '../services/authorize.php';
authorize(['SUPER_ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../exceptions/forbidden.php');
    exit();
}

require_once '../security/csrf.php';
requireValidCsrfToken('../pages/home.php?error=invalid_request');
$adminId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$newPassword = trim($_POST['new_password'] ?? '');

if (!$adminId || $newPassword === '') {
    header('Location: ../pages/home.php?error=invalid_reset_password');
    exit();
}

require '../lib/connection.php';

try {
    // Your current sample database stores plain text passwords.
    // This keeps it compatible with your current login code.
    $passwordForDatabase = $newPassword;

    $sqlStatement = $conn->prepare("UPDATE users
        SET password = ?
        WHERE id = ?
          AND role = 'ADMIN'
          AND status = 'ACTIVE'");

    if (!$sqlStatement) {
        throw new Exception($conn->error);
    }

    $sqlStatement->bind_param('si', $passwordForDatabase, $adminId);

    if (!$sqlStatement->execute()) {
        throw new Exception($sqlStatement->error);
    }

    header('Location: ../pages/home.php?success=password_reset');
    exit();
} catch (Exception $e) {
    error_log('Reset admin password failed: ' . $e->getMessage());
    header('Location: ../exceptions/internalServerError.php');
    exit();
} finally {
    if (isset($sqlStatement)) {
        $sqlStatement->close();
    }

    if (isset($conn)) {
        $conn->close();
    }
}
?>
