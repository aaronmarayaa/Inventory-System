<?php
    session_start();
    $somethingWentWrong = false;
    $executionFailed = false;
    $loginFailed = false;

    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $somethingWentWrong = true;
    } 
    require "../lib/connection.php";

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? null;
    $password = $_POST['password'] ?? null;

    try {
        $sqlStatement = $conn->prepare(
            "SELECT id, first_name, last_name, role, password, status FROM users WHERE email = ?"
        );

        $sqlStatement->bind_param("s", $email);
        if(!$sqlStatement->execute()) {
            $executionFailed = true;
            exit();
        }

        $result = $sqlStatement->get_result();
        if ($result->num_rows === 0) {
            $loginFailed = true;
            echo "user not found!";
            exit();
        }

        $user = $result->fetch_assoc();
        if ($user['status'] !== 'ACTIVE') {
            die("Account is archived.");
        }
        if ($password !== $user['password']) {
            die("Invalid password");
        }

        $_SESSION['loginSuccess'] = true;
        $_SESSION['role'] = $user['role'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        header("Location: ../pages/home.php");
    } catch(Exception $exception) {
        error_log("Error: " . $conn->error);
        $somethingWentWrong = false;
    } finally {
        if(isset($sqlStatement)) { $sqlStatement->close(); }
        if(isset($conn)) { $conn->close(); }
    }
?>
