<?php
    require_once '../services/authorize.php';
    authorize(['ADMIN']);

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

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || trim($password) === '') {
        redirectHome('error=invalid_user_input');
    }

    try {
        $checkStatement = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');

        if (!$checkStatement) {
            throw new Exception('Prepare email check failed: ' . $conn->error);
        }

        $checkStatement->bind_param('s', $email);
        $checkStatement->execute();
        $existingUser = $checkStatement->get_result();

        if ($existingUser && $existingUser->num_rows > 0) {
            redirectHome('error=email_exists');
        }

        $passwordForDatabase = $password;
        $role = 'REGULAR';
        $status = 'ACTIVE';

        $insertStatement = $conn->prepare('INSERT INTO users
            (first_name, last_name, email, password, role, status)
            VALUES (?, ?, ?, ?, ?, ?)');

        if (!$insertStatement) {
            throw new Exception('Prepare user insert failed: ' . $conn->error);
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
            throw new Exception('User insert failed: ' . $insertStatement->error);
        }

        redirectHome('success=user_added');
    } catch (Exception $e) {
        error_log('Admin add user failed: ' . $e->getMessage());
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
