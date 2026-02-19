<?php
    require '../services/authorize.php';
    authorize(['REGULAR']);

    require "../lib/connection.php";

    $firstName = $_SESSION["first_name"];
    $userId = $_SESSION["id"];

    try {
        $sqlStatement = $conn->prepare("SELECT ci.id AS inventory_id,
            ci.quantity,
            ci.status,
            ci.created_by,
            ci.approved_by,
            ci.created_at,
            ci.chocolate_id,
            c.chocolate_name,
            c.image_path
        FROM chocolate_inventory ci
        JOIN chocolate_items c ON ci.chocolate_id = c.id
        WHERE ci.status = 'ACTIVE'
        AND ci.created_by = ?");

        $sqlStatement->bind_param("i", $userId);
        $sqlStatement->execute();
        $result = $sqlStatement->get_result();
        
        if ($result->num_rows == 0) {
            echo "No approved chocolates found.";
        }
    } catch(Exception $e) {
        error_log("Retrieving Item failed: " . $sqlStatement->error);
        header("Location: ../exceptions/internalServerError.php");
        exit();
    } finally {
        if(isset($sqlStatement)) { $sqlStatement->close(); }
        if(isset($conn)) { $conn->close(); }
    }
?>