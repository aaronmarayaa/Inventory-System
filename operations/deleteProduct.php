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
        $oldStatus = strtoupper($row['status'] ?? '');

        if ($oldStatus === 'PENDING') {
            $deleteStatement = $conn->prepare("DELETE FROM chocolate_inventory
                WHERE id = ?
                  AND UPPER(status) = 'PENDING'");

            if (!$deleteStatement) {
                throw new Exception('Prepare pending product delete failed: ' . $conn->error);
            }

            $deleteStatement->bind_param('i', $inventoryId);

            if (!$deleteStatement->execute()) {
                throw new Exception('Pending product delete failed: ' . $deleteStatement->error);
            }

            if ($deleteStatement->affected_rows === 0) {
                header('Location: ../pages/home.php?error=invalid_product');
                exit();
            }

            header('Location: ../pages/home.php?success=item_rejected');
            exit();
        }

        $newStatus = 'ARCHIVED';

        $archiveStatement = $conn->prepare("UPDATE chocolate_inventory
            SET status = ?
            WHERE id = ?
              AND UPPER(status) = 'ACTIVE'");

        if (!$archiveStatement) {
            throw new Exception('Prepare admin product archive failed: ' . $conn->error);
        }

        $archiveStatement->bind_param('si', $newStatus, $inventoryId);

        if (!$archiveStatement->execute()) {
            throw new Exception('Product archive failed: ' . $archiveStatement->error);
        }

        if ($archiveStatement->affected_rows === 0) {
            header('Location: ../pages/home.php?error=invalid_product');
            exit();
        }

        header('Location: ../pages/home.php?success=item_archived');
        exit();
    } catch (Exception $e) {
        error_log('Admin archive/reject product failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($checkStatement)) {
            $checkStatement->close();
        }

        if (isset($deleteStatement)) {
            $deleteStatement->close();
        }

        if (isset($archiveStatement)) {
            $archiveStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
