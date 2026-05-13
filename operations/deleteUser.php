<?php
    require_once '../services/authorize.php';
    authorize(['ADMIN']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../exceptions/forbidden.php');
        exit();
    }

    require_once '../security/csrf.php';
    requireValidCsrfToken('../pages/home.php?error=invalid_request');

    $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($userId === false || $userId === null || $userId <= 0) {
        header('Location: ../pages/home.php?error=invalid_user');
        exit();
    }

    require '../lib/connection.php';

    try {
        $status = 'ARCHIVED';

        $archiveStatement = $conn->prepare("UPDATE users
            SET status = ?
            WHERE id = ?
              AND role = 'REGULAR'
              AND status = 'ACTIVE'");

        if (!$archiveStatement) {
            throw new Exception('Prepare user archive failed: ' . $conn->error);
        }

        $archiveStatement->bind_param('si', $status, $userId);

        if (!$archiveStatement->execute()) {
            throw new Exception('User archive failed: ' . $archiveStatement->error);
        }

        if ($archiveStatement->affected_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_user');
            exit();
        }

        header('Location: ../pages/home.php?success=user_archived');
        exit();
    } catch (Exception $e) {
        error_log('Admin archive user failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($archiveStatement)) {
            $archiveStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
