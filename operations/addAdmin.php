<?php
require_once '../services/authorize.php';
authorize(['SUPER_ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../exceptions/forbidden.php');
    exit();
}

require '../lib/connection.php';

function redirectHome(string $query): void {
    header('Location: ../pages/home.php?' . $query);
    exit();
}

$existingUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

try {
    if ($existingUserId) {
        $promoteStatement = $conn->prepare("UPDATE users
            SET role = 'ADMIN', status = 'ACTIVE'
            WHERE id = ?
              AND role = 'REGULAR'
              AND status = 'ACTIVE'");

        if (!$promoteStatement) {
            throw new Exception($conn->error);
        }

        $promoteStatement->bind_param('i', $existingUserId);

        if (!$promoteStatement->execute()) {
            throw new Exception($promoteStatement->error);
        }

        redirectHome('success=admin_promoted');
    }

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || trim($password) === '' || trim($confirmPassword) === '') {
        redirectHome('error=invalid_admin_input');
    }

    if ($password !== $confirmPassword) {
        redirectHome('error=password_mismatch');
    }

    $checkStatement = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');

    if (!$checkStatement) {
        throw new Exception($conn->error);
    }

    $checkStatement->bind_param('s', $email);
    $checkStatement->execute();
    $existingUser = $checkStatement->get_result();

    if ($existingUser && $existingUser->num_rows > 0) {
        redirectHome('error=email_exists');
    }

    $passwordForDatabase = $password;
    $role = 'ADMIN';
    $status = 'ACTIVE';

    $insertStatement = $conn->prepare('INSERT INTO users
        (first_name, last_name, email, password, role, status)
        VALUES (?, ?, ?, ?, ?, ?)');

    if (!$insertStatement) {
        throw new Exception($conn->error);
    }

    $insertStatement->bind_param(
        'ssssss',
        $firstName,
        $lastName,
        $email,
        $passwordForDatabase,
        $role,
        $status
    );

    if (!$insertStatement->execute()) {
        throw new Exception($insertStatement->error);
    }

    redirectHome('success=admin_added');
} catch (Exception $e) {
    error_log('Add admin failed: ' . $e->getMessage());
    header('Location: ../exceptions/internalServerError.php');
    exit();
} finally {
    if (isset($promoteStatement)) {
        $promoteStatement->close();
    }

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
