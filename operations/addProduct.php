<?php
    require_once '../services/authorize.php';
    authorize(['REGULAR', 'ADMIN']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../exceptions/forbidden.php');
        exit();
    }

    require_once '../security/csrf.php';
    requireValidCsrfToken('../pages/home.php?error=invalid_request');

    $userId = (int) $_SESSION['id'];
    $currentRole = $_SESSION['role'] ?? '';
    $chocolateId = filter_input(INPUT_POST, 'chocolateItem', FILTER_VALIDATE_INT);

    $quantityInput = $_POST['chocolateQuantity'] ?? ($_POST['quantity'] ?? null);
    $quantity = filter_var($quantityInput, FILTER_VALIDATE_INT);

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

        $status = $currentRole === 'ADMIN' ? 'ACTIVE' : 'PENDING';

        $insertStatement = $conn->prepare('INSERT INTO chocolate_inventory
            (chocolate_id, quantity, status, created_by)
            VALUES (?, ?, ?, ?)');

        if (!$insertStatement) {
            throw new Exception('Prepare product insert failed: ' . $conn->error);
        }

        $insertStatement->bind_param('iisi', $chocolateId, $quantity, $status, $userId);

        if (!$insertStatement->execute()) {
            throw new Exception('Product insert failed: ' . $insertStatement->error);
        }

        $success = $currentRole === 'ADMIN' ? 'item_added' : 'product_pending';
        header('Location: ../pages/home.php?success=' . $success);
        exit();
    } catch (Exception $e) {
        error_log('Add product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($checkStatement)) {
            $checkStatement->close();
        }

        if (isset($insertStatement)) {
            $insertStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
