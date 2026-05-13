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

    function createUuidV4(): string {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    function usersTableHasColumn(mysqli $conn, string $column): bool {
        $columnCheck = $conn->prepare('SHOW COLUMNS FROM users LIKE ?');

        if (!$columnCheck) {
            throw new Exception('Prepare column check failed: ' . $conn->error);
        }

        $columnCheck->bind_param('s', $column);
        $columnCheck->execute();
        $result = $columnCheck->get_result();
        $hasColumn = $result && $result->num_rows > 0;
        $columnCheck->close();

        return $hasColumn;
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

        if (usersTableHasColumn($conn, 'uuid')) {
            $uuid = createUuidV4();
            $insertStatement = $conn->prepare('INSERT INTO users
                (uuid, first_name, last_name, email, password, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)');

            if (!$insertStatement) {
                throw new Exception('Prepare user insert with uuid failed: ' . $conn->error);
            }

            $insertStatement->bind_param('sssssss', $uuid, $firstName, $lastName, $email, $passwordForDatabase, $role, $status);
        } else {
            $insertStatement = $conn->prepare('INSERT INTO users
                (first_name, last_name, email, password, role, status)
                VALUES (?, ?, ?, ?, ?, ?)');

            if (!$insertStatement) {
                throw new Exception('Prepare user insert failed: ' . $conn->error);
            }

            $insertStatement->bind_param('ssssss', $firstName, $lastName, $email, $passwordForDatabase, $role, $status);
        }

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
