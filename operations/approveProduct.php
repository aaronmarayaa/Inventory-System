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

        $approveStatement = $conn->prepare("UPDATE chocolate_inventory
            SET status = ?
            WHERE id = ?
              AND status = 'PENDING'");

        if (!$approveStatement) {
            throw new Exception('Prepare approve product failed: ' . $conn->error);
        }

        $approveStatement->bind_param('si', $status, $inventoryId);

        if (!$approveStatement->execute()) {
            throw new Exception('Approve product failed: ' . $approveStatement->error);
        }

        if ($approveStatement->affected_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_item');
            exit();
        }

        header('Location: ../pages/home.php?success=item_approved');
        exit();
    } catch (Exception $e) {
        error_log('Approve product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($approveStatement)) {
            $approveStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
