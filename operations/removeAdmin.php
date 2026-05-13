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

    if ($adminId === false || $adminId === null || $adminId <= 0) {
        header('Location: ../pages/home.php?error=invalid_admin');
        exit();
    }

    require '../lib/connection.php';

    try {
        $status = 'ARCHIVED';

        $removeStatement = $conn->prepare("UPDATE users
            SET status = ?
            WHERE id = ?
              AND role = 'ADMIN'
              AND status = 'ACTIVE'");

        if (!$removeStatement) {
            throw new Exception('Prepare admin archive failed: ' . $conn->error);
        }

        $removeStatement->bind_param('si', $status, $adminId);

        if (!$removeStatement->execute()) {
            throw new Exception('Admin archive failed: ' . $removeStatement->error);
        }

        if ($removeStatement->affected_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_admin');
            exit();
        }

        header('Location: ../pages/home.php?success=admin_removed');
        exit();
    } catch (Exception $e) {
        error_log('Remove admin failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($removeStatement)) {
            $removeStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
