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
    $chocolateId = filter_input(INPUT_POST, 'chocolateItem', FILTER_VALIDATE_INT);

    $quantityInput = $_POST['quantity'] ?? ($_POST['chocolateQuantity'] ?? null);
    $quantity = filter_var($quantityInput, FILTER_VALIDATE_INT);

    if ($inventoryId === false || $inventoryId === null || $inventoryId <= 0) {
        header('Location: ../pages/home.php?error=invalid_product');
        exit();
    }

    if ($chocolateId === false || $chocolateId === null || $chocolateId <= 0) {
        header('Location: ../pages/home.php?error=invalid_chocolate');
        exit();
    }

    if ($quantity === false || $quantity === null || $quantity <= 0) {
        header('Location: ../pages/home.php?error=invalid_quantity');
        exit();
    }

    require '../lib/connection.php';

    try {
        $checkChocolateStatement = $conn->prepare('SELECT id FROM chocolate_items WHERE id = ? LIMIT 1');

        if (!$checkChocolateStatement) {
            throw new Exception('Prepare chocolate check failed: ' . $conn->error);
        }

        $checkChocolateStatement->bind_param('i', $chocolateId);
        $checkChocolateStatement->execute();
        $checkChocolateResult = $checkChocolateStatement->get_result();

        if ($checkChocolateResult->num_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_chocolate');
            exit();
        }

        $updateStatement = $conn->prepare("UPDATE chocolate_inventory
            SET chocolate_id = ?, quantity = ?
            WHERE id = ?
              AND status = 'ACTIVE'");

        if (!$updateStatement) {
            throw new Exception('Prepare admin product update failed: ' . $conn->error);
        }

        $updateStatement->bind_param('iii', $chocolateId, $quantity, $inventoryId);

        if (!$updateStatement->execute()) {
            throw new Exception('Product update failed: ' . $updateStatement->error);
        }

        if ($updateStatement->affected_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_product');
            exit();
        }

        header('Location: ../pages/home.php?success=item_updated');
        exit();
    } catch (Exception $e) {
        error_log('Admin update product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($checkChocolateStatement)) {
            $checkChocolateStatement->close();
        }

        if (isset($updateStatement)) {
            $updateStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
