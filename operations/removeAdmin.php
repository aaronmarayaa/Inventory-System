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

if (!$adminId) {
    header('Location: ../pages/home.php?error=invalid_admin');
    exit();
}

require '../lib/connection.php';

try {
    $status = 'ARCHIVED';

    $sqlStatement = $conn->prepare("UPDATE users
        SET status = ?
        WHERE id = ?
          AND role = 'ADMIN'
          AND status = 'ACTIVE'");

    if (!$sqlStatement) {
        throw new Exception($conn->error);
    }

    $sqlStatement->bind_param('si', $status, $adminId);

    if (!$sqlStatement->execute()) {
        throw new Exception($sqlStatement->error);
    }

    header('Location: ../pages/home.php?success=admin_removed');
    exit();
} catch (Exception $e) {
    error_log('Remove admin failed: ' . $e->getMessage());
    header('Location: ../exceptions/internalServerError.php');
    exit();
} finally {
    if (isset($sqlStatement)) {
        $sqlStatement->close();
    }

    if (isset($conn)) {
        $conn->close();
    }
}
?>
