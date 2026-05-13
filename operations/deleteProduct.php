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
        header('Location: ../pages/home.php?error=invalid_product');
        exit();
    }

    require '../lib/connection.php';

    try {
        $checkStatement = $conn->prepare("SELECT status FROM chocolate_inventory
            WHERE id = ?
              AND status IN ('ACTIVE', 'PENDING')
            LIMIT 1");

        if (!$checkStatement) {
            throw new Exception('Prepare admin product check failed: ' . $conn->error);
        }

        $checkStatement->bind_param('i', $inventoryId);
        $checkStatement->execute();
        $checkResult = $checkStatement->get_result();

        if ($checkResult->num_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_product');
            exit();
        }

        $row = $checkResult->fetch_assoc();
        $oldStatus = $row['status'] ?? '';
        $newStatus = 'ARCHIVED';

        $archiveStatement = $conn->prepare("UPDATE chocolate_inventory
            SET status = ?
            WHERE id = ?
              AND status IN ('ACTIVE', 'PENDING')");

        if (!$archiveStatement) {
            throw new Exception('Prepare admin product archive/reject failed: ' . $conn->error);
        }

        $archiveStatement->bind_param('si', $newStatus, $inventoryId);

        if (!$archiveStatement->execute()) {
            throw new Exception('Product archive/reject failed: ' . $archiveStatement->error);
        }

        if ($archiveStatement->affected_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_product');
            exit();
        }

        $success = $oldStatus === 'PENDING' ? 'item_rejected' : 'item_archived';

        header('Location: ../pages/home.php?success=' . $success);
        exit();
    } catch (Exception $e) {
        error_log('Admin archive/reject product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($checkStatement)) {
            $checkStatement->close();
        }

        if (isset($archiveStatement)) {
            $archiveStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
