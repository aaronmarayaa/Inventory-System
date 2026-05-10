<?php
    require_once '../services/authorize.php';
    authorize(['REGULAR']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../exceptions/forbidden.php');
        exit();
    }

    $userId = (int) $_SESSION['id'];
    $inventoryId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($inventoryId === false || $inventoryId === null || $inventoryId <= 0) {
        header('Location: ../pages/home.php?error=invalid_product');
        exit();
    }

    require '../lib/connection.php';

    try {
        // Soft delete only the logged-in user's own PENDING request.
        // This keeps approved inventory/history intact.
        $status = 'ARCHIVED';

        $deleteStatement = $conn->prepare("UPDATE chocolate_inventory
            SET status = ?
            WHERE id = ?
              AND created_by = ?
              AND status = 'PENDING'");

        if (!$deleteStatement) {
            throw new Exception('Prepare product delete failed: ' . $conn->error);
        }

        $deleteStatement->bind_param('sii', $status, $inventoryId, $userId);

        if (!$deleteStatement->execute()) {
            throw new Exception('Product delete failed: ' . $deleteStatement->error);
        }

        header('Location: ../pages/home.php?success=product_deleted');
        exit();
    } catch (Exception $e) {
        error_log('Regular delete product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($deleteStatement)) {
            $deleteStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
