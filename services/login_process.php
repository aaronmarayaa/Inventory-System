<?php
    session_start();

    require '../lib/connection.php';
    require_once '../security/csrf.php';
    require_once '../security/rememberMe.php';

    $loginPage = '../index.php';

    function redirectWithLoginError(string $message, string $loginPage): void {
        $_SESSION['login_error'] = $message;
        header('Location: ' . $loginPage);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirectWithLoginError('Something went wrong. Please try again.', $loginPage);
    }

    validateCsrfToken($loginPage . '?error=invalid_request');

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || trim($password) === '') {
        redirectWithLoginError('Invalid credentials.', $loginPage);
    }

    try {
        $sqlStatement = $conn->prepare(
            'SELECT id, first_name, last_name, role, password, status FROM users WHERE email = ? LIMIT 1'
        );

        if (!$sqlStatement) {
            error_log('Prepare failed: ' . $conn->error);
            redirectWithLoginError('Something went wrong. Please try again.', $loginPage);
        }

        $sqlStatement->bind_param('s', $email);

        if (!$sqlStatement->execute()) {
            error_log('Execute failed: ' . $sqlStatement->error);
            redirectWithLoginError('Something went wrong. Please try again.', $loginPage);
        }

        $result = $sqlStatement->get_result();

        if ($result->num_rows === 0) {
            redirectWithLoginError('User not found.', $loginPage);
        }

        $user = $result->fetch_assoc();

        if ($user['status'] !== 'ACTIVE') {
            redirectWithLoginError('Account is archived.', $loginPage);
        }

        $storedPassword = $user['password'];
        $passwordMatches = password_verify($password, $storedPassword);

        if (!$passwordMatches && hash_equals($storedPassword, $password)) {
            $passwordMatches = true;

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');

            if ($updatePassword) {
                $updatePassword->bind_param('si', $hashedPassword, $user['id']);
                $updatePassword->execute();
                $updatePassword->close();
            }
        }

        if (!$passwordMatches) {
            redirectWithLoginError('Invalid credentials.', $loginPage);
        }

        unset($_SESSION['login_error']);

        setUserSession($user);

        if (rememberMeChecked()) {
            createRememberMeToken($conn, (int) $user['id']);
        } else {
            deleteRememberMeToken($conn);
            clearRememberMeCookie();
        }

        header('Location: ../pages/home.php');
        exit();
    } catch (Exception $exception) {
        error_log('Login error: ' . $exception->getMessage());
        redirectWithLoginError('Something went wrong. Please try again.', $loginPage);
    } finally {
        if (isset($sqlStatement)) {
            $sqlStatement->close();
        }

        if (isset($conn)) {
            $conn->close();
        }
    }
?>
