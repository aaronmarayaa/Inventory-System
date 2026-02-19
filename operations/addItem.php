<?php
    require "../services/authorize.php";
    authorize(["ADMIN", "REGULAR"]);

    $userId = $_SESSION["id"];

    $chocolateId = filter_input(INPUT_POST, 'chocolateItem', FILTER_VALIDATE_INT);;
    $chocolateQuantity = filter_input(INPUT_POST, 'chocolateQuantity', FILTER_VALIDATE_INT);

    $invalidQuantity = false;
    $invalidChocolateName = false;

    if (!$chocolateId) {
        $invalidChocolateId = true;
        exit;
    }

    if ($chocolateQuantity == false || $chocolateQuantity <= 0) {
        $invalidQuantity = true;
        exit;
    }
    
    require "../lib/connection.php";

    try {
        $sqlStatement = $conn->prepare("INSERT INTO chocolate_inventory
        (chocolate_id, quantity, status, created_by)
        VALUES (?, ?, 'PENDING', ?)");

        if(!$sqlStatement) {
            error_log("Prepare failed: " . $conn->error);
            header("Location: ../exceptions/internalServerError.php");
            exit();
        }

        $sqlStatement->bind_param("iii", $chocolateId, $chocolateQuantity, $userId);

        if (!$sqlStatement->execute()) {
            error_log("Adding Item failed: " . $sqlStatement->error);
            header("Location: ../exceptions/internalServerError.php");
            exit;
        }
    } catch(Exception $e) {
        error_log("Database Error: " . $e->getMessage());
        header("Location: ../exceptions/internalServerError.php");
        exit;
    } finally {
        if(isset($sqlStatement)) { $sqlStatement->close(); }
        if(isset($conn)) { $conn->close(); }
    }
?>