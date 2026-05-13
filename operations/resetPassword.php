<?php
    require_once '../services/authorize.php';
    authorize(['SUPER_ADMIN', 'ADMIN']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../exceptions/forbidden.php');
        exit();
    }

    require_once '../security/csrf.php';
    requireValidCsrfToken('../pages/home.php?error=invalid_request');

    $currentRole = $_SESSION['role'] ?? '';
    $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $newPassword = trim($_POST['new_password'] ?? '');

    if ($userId === false || $userId === null || $userId <= 0 || $newPassword === '') {
        header('Location: ../pages/home.php?error=invalid_reset_password');
        exit();
    }

    $targetRole = $currentRole === 'SUPER_ADMIN' ? 'ADMIN' : 'REGULAR';
    $successMessage = $currentRole === 'SUPER_ADMIN' ? 'password_reset' : 'user_password_reset';

    require '../lib/connection.php';

    try {
        $passwordForDatabase = $newPassword;

        $resetStatement = $conn->prepare("UPDATE users
            SET password = ?
            WHERE id = ?
              AND role = ?
              AND status = 'ACTIVE'");

        if (!$resetStatement) {
            throw new Exception('Prepare password reset failed: ' . $conn->error);
        }

        $resetStatement->bind_param('sis', $passwordForDatabase, $userId, $targetRole);

        if (!$resetStatement->execute()) {
            throw new Exception('Password reset failed: ' . $resetStatement->error);
        }

        if ($resetStatement->affected_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_user');
            exit();
        }

        header('Location: ../pages/home.php?success=' . $successMessage);
        exit();
    } catch (Exception $e) {
        error_log('Reset password failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($resetStatement)) {
            $resetStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
