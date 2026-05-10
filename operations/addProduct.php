<?php
    require_once '../services/authorize.php';
    authorize(['REGULAR']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../exceptions/forbidden.php');
        exit();
    }

    $userId = (int) $_SESSION['id'];
    $chocolateId = filter_input(INPUT_POST, 'chocolateItem', FILTER_VALIDATE_INT);
    $chocolateQuantity = filter_input(INPUT_POST, 'chocolateQuantity', FILTER_VALIDATE_INT);

    if ($chocolateId === false || $chocolateId === null || $chocolateId <= 0) {
        header('Location: ../pages/home.php?error=invalid_chocolate');
        exit();
    }

    if ($chocolateQuantity === false || $chocolateQuantity === null || $chocolateQuantity <= 0) {
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

        // Keep each request as its own row.
        // It will be included in the grouped total only after an admin approves it.
        $status = 'PENDING';

        $insertStatement = $conn->prepare('INSERT INTO chocolate_inventory
            (chocolate_id, quantity, status, created_by)
            VALUES (?, ?, ?, ?)');

        if (!$insertStatement) {
            throw new Exception('Prepare product insert failed: ' . $conn->error);
        }

        $insertStatement->bind_param('iisi', $chocolateId, $chocolateQuantity, $status, $userId);

        if (!$insertStatement->execute()) {
            throw new Exception('Product insert failed: ' . $insertStatement->error);
        }

        header('Location: ../pages/home.php?success=product_pending');
        exit();
    } catch (Exception $e) {
        error_log('Regular add product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($insertStatement)) {
            $insertStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
