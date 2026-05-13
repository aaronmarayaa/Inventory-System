<?php
    require_once '../services/authorize.php';
    authorize(['ADMIN']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../exceptions/forbidden.php');
        exit();
    }

    require_once '../security/csrf.php';
    requireValidCsrfToken('../pages/home.php?error=invalid_request');

    $inventoryId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($inventoryId === false || $inventoryId === null || $inventoryId <= 0) {
        header('Location: ../pages/home.php?error=invalid_item');
        exit();
    }

    require '../lib/connection.php';

    try {
        $status = 'ACTIVE';

        $restoreStatement = $conn->prepare("UPDATE chocolate_inventory
            SET status = ?
            WHERE id = ?
              AND status = 'ARCHIVED'");

        if (!$restoreStatement) {
            throw new Exception('Prepare restore product failed: ' . $conn->error);
        }

        $restoreStatement->bind_param('si', $status, $inventoryId);

        if (!$restoreStatement->execute()) {
            throw new Exception('Restore product failed: ' . $restoreStatement->error);
        }

        if ($restoreStatement->affected_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_item');
            exit();
        }

        header('Location: ../pages/home.php?success=item_restored');
        exit();
    } catch (Exception $e) {
        error_log('Restore product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($restoreStatement)) {
            $restoreStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
