<?php
    require_once '../services/authorize.php';
    authorize(['REGULAR']);

    require '../lib/connection.php';

    $firstName = $_SESSION['first_name'];
    $userId = (int) $_SESSION['id'];

    $activeProducts = [];
    $pendingProducts = [];
    $chocolateOptions = [];

    try {
        // Main product list: combine all ACTIVE inventory rows by chocolate.
        // Example: if Mark adds 75 Ritter Sport and Alice's approved request adds 20,
        // the website shows one Ritter Sport card with Quantity: 95.
        $activeProductsStatement = "SELECT
                c.id AS chocolate_id,
                c.chocolate_name,
                c.image_path,
                SUM(ci.quantity) AS total_quantity,
                COUNT(ci.id) AS total_records,
                COUNT(DISTINCT ci.created_by) AS total_contributors,
                GROUP_CONCAT(
                    DISTINCT CONCAT(u.first_name, ' ', u.last_name)
                    ORDER BY u.first_name, u.last_name
                    SEPARATOR ', '
                ) AS contributors
            FROM chocolate_inventory ci
            JOIN chocolate_items c ON ci.chocolate_id = c.id
            JOIN users u ON ci.created_by = u.id
            WHERE ci.status = 'ACTIVE'
            GROUP BY c.id, c.chocolate_name, c.image_path
            ORDER BY c.chocolate_name ASC";

        $activeProductsResult = $conn->query($activeProductsStatement);

        if (!$activeProductsResult) {
            throw new Exception('Retrieve grouped active products failed: ' . $conn->error);
        }

        while ($row = $activeProductsResult->fetch_assoc()) {
            $activeProducts[] = $row;
        }

        // Keep each regular user's pending request separate for tracking/update/delete.
        $pendingProductsStatement = $conn->prepare("SELECT
                ci.id AS inventory_id,
                ci.quantity,
                ci.status,
                ci.created_by,
                ci.created_at,
                ci.chocolate_id,
                c.chocolate_name,
                c.image_path
            FROM chocolate_inventory ci
            JOIN chocolate_items c ON ci.chocolate_id = c.id
            WHERE ci.status = 'PENDING'
              AND ci.created_by = ?
            ORDER BY ci.created_at DESC, ci.id DESC");

        if (!$pendingProductsStatement) {
            throw new Exception('Prepare pending products query failed: ' . $conn->error);
        }

        $pendingProductsStatement->bind_param('i', $userId);
        $pendingProductsStatement->execute();
        $pendingProductsResult = $pendingProductsStatement->get_result();

        while ($row = $pendingProductsResult->fetch_assoc()) {
            $pendingProducts[] = $row;
        }

        $pendingProductsStatement->close();

        // Dropdown source for add/update request forms.
        $chocolateOptionsResult = $conn->query("SELECT id, chocolate_name FROM chocolate_items ORDER BY chocolate_name ASC");

        if (!$chocolateOptionsResult) {
            throw new Exception('Retrieve chocolate dropdown failed: ' . $conn->error);
        }

        while ($row = $chocolateOptionsResult->fetch_assoc()) {
            $chocolateOptions[] = $row;
        }
    } catch (Exception $e) {
        error_log('Regular user dashboard data retrieval failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
?>
