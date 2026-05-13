<?php
require_once '../services/authorize.php';
authorize(['SUPER_ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../exceptions/forbidden.php');
    exit();
}

require_once '../security/csrf.php';
requireValidCsrfToken('../pages/home.php?error=invalid_request');
require '../lib/connection.php';

function redirectHome(string $query): void {
    header('Location: ../pages/home.php?' . $query);
    exit();
}

function createUuidV4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$existingUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

try {
    // Promote an existing REGULAR user to ADMIN.
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

    // Create a new ADMIN account from the modal.
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || trim($password) === '') {
        redirectHome('error=invalid_admin_input');
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

    // Your current sample database stores plain text passwords.
    // This keeps it compatible with your current login code.
    $passwordForDatabase = $password;
    $uuid = createUuidV4();
    $role = 'ADMIN';
    $status = 'ACTIVE';

    $insertStatement = $conn->prepare('INSERT INTO users
        (uuid, first_name, last_name, email, password, role, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)');

    if (!$insertStatement) {
        throw new Exception($conn->error);
    }

    $insertStatement->bind_param(
        'sssssss',
        $uuid,
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
