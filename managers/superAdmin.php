<?php
require_once '../services/authorize.php';
authorize(['SUPER_ADMIN']);

require '../lib/connection.php';

$adminUsers = [];
$regularUsers = [];

try {
    $selectAdminsStatement = "SELECT
            id,
            first_name,
            last_name,
            email,
            role,
            status,
            created_at
        FROM users
        WHERE role = 'ADMIN'
          AND status = 'ACTIVE'
        ORDER BY first_name ASC, last_name ASC";

    $adminResult = $conn->query($selectAdminsStatement);

    if (!$adminResult) {
        throw new Exception($conn->error);
    }

    $adminUsers = $adminResult->fetch_all(MYSQLI_ASSOC);

    $selectRegularUsersStatement = "SELECT
            id,
            first_name,
            last_name,
            email,
            role,
            status,
            created_at
        FROM users
        WHERE role = 'REGULAR'
          AND status = 'ACTIVE'
        ORDER BY first_name ASC, last_name ASC";

    $regularUserResult = $conn->query($selectRegularUsersStatement);

    if (!$regularUserResult) {
        throw new Exception($conn->error);
    }

    $regularUsers = $regularUserResult->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log('Retrieving super admin users failed: ' . $e->getMessage());
    header('Location: ../exceptions/internalServerError.php');
    exit();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
