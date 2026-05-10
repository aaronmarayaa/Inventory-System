<?php
    require_once '../services/authorize.php';
    authorize(['REGULAR']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../exceptions/forbidden.php');
        exit();
    }

    $userId = (int) $_SESSION['id'];
    $inventoryId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $chocolateId = filter_input(INPUT_POST, 'chocolateItem', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

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
        $checkStatement = $conn->prepare('SELECT id FROM chocolate_items WHERE id = ? LIMIT 1');

        if (!$checkStatement) {
            throw new Exception('Prepare chocolate check failed: ' . $conn->error);
        }

        $checkStatement->bind_param('i', $chocolateId);
        $checkStatement->execute();
        $checkResult = $checkStatement->get_result();

        if ($checkResult->num_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_chocolate');
            exit();
        }

        $checkStatement->close();

        // Regular users can only edit their own PENDING requests.
        // ACTIVE inventory is grouped for display and should be changed through admin approval/history flows.
        $updateStatement = $conn->prepare("UPDATE chocolate_inventory
            SET chocolate_id = ?, quantity = ?
            WHERE id = ?
              AND created_by = ?
              AND status = 'PENDING'");

        if (!$updateStatement) {
            throw new Exception('Prepare product update failed: ' . $conn->error);
        }

        $updateStatement->bind_param('iiii', $chocolateId, $quantity, $inventoryId, $userId);

        if (!$updateStatement->execute()) {
            throw new Exception('Product update failed: ' . $updateStatement->error);
        }

        header('Location: ../pages/home.php?success=product_updated');
        exit();
    } catch (Exception $e) {
        error_log('Regular update product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($updateStatement)) {
            $updateStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
