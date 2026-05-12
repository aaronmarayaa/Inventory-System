<?php
    session_start();

    require_once __DIR__ . '/../security/csrf.php';
    require "../lib/connection.php";

    // login_process.php is inside /services, so ../index.php goes back to your login page.
    $loginPage = "../index.php";

    function redirectWithLoginError(string $message, string $loginPage): void {
        $_SESSION['login_error'] = $message;
        header("Location: " . $loginPage);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirectWithLoginError("Something went wrong. Please try again.", $loginPage);
    }

    if (!isCsrfTokenValid()) {
        redirectWithLoginError("Invalid request. Please refresh the page and try again.", $loginPage);
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || trim($password) === '') {
        redirectWithLoginError("Invalid credentials.", $loginPage);
    }

    try {
        $sqlStatement = $conn->prepare(
            "SELECT id, first_name, last_name, role, password, status FROM users WHERE email = ?"
        );

        if (!$sqlStatement) {
            error_log("Prepare failed: " . $conn->error);
            redirectWithLoginError("Something went wrong. Please try again.", $loginPage);
        }

        $sqlStatement->bind_param("s", $email);

        if (!$sqlStatement->execute()) {
            error_log("Execute failed: " . $sqlStatement->error);
            redirectWithLoginError("Something went wrong. Please try again.", $loginPage);
        }

        $result = $sqlStatement->get_result();

        if ($result->num_rows === 0) {
            redirectWithLoginError("User not found.", $loginPage);
        }

        $user = $result->fetch_assoc();

        if ($user['status'] !== 'ACTIVE') {
            redirectWithLoginError("Account is archived.", $loginPage);
        }

        // Your current system appears to store plain-text passwords.
        // If you later use password_hash(), replace this condition with password_verify().
        if ($password !== $user['password']) {
            redirectWithLoginError("Invalid credentials.", $loginPage);
        }

        unset($_SESSION['login_error']);

        $_SESSION['loginSuccess'] = true;
        $_SESSION['role'] = $user['role'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        header("Location: ../pages/home.php");
        exit();
    } catch (Exception $exception) {
        error_log("Login error: " . $exception->getMessage());
        redirectWithLoginError("Something went wrong. Please try again.", $loginPage);
    } finally {
        if (isset($sqlStatement)) { $sqlStatement->close(); }
        if (isset($conn)) { $conn->close(); }
    }
?>
