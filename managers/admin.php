<?php
    require_once '../services/authorize.php';
    authorize(['ADMIN']);

    require '../lib/connection.php';

    $firstName = $_SESSION['first_name'];
    $userId = (int) $_SESSION['id'];

    $activeProducts = [];
    $pendingItems = [];
    $regularUsers = [];

    try {
        // Admin home display: grouped ACTIVE quantities by chocolate.
        // Admin pending approval remains separate rows below.
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

        $selectItemsStatement = "SELECT
                ci.id AS inventory_id,
                ci.quantity,
                ci.status,
                ci.created_by,
                ci.created_at,
                ci.chocolate_id,
                c.chocolate_name,
                c.image_path,
                u.first_name,
                u.last_name,
                u.email,
                u.role
            FROM chocolate_inventory ci
            JOIN chocolate_items c ON ci.chocolate_id = c.id
            JOIN users u ON ci.created_by = u.id
            WHERE ci.status = 'PENDING'
            ORDER BY ci.created_at DESC, ci.id DESC";

        $resultItem = $conn->query($selectItemsStatement);

        if (!$resultItem) {
            throw new Exception('Retrieve pending items failed: ' . $conn->error);
        }

        while ($row = $resultItem->fetch_assoc()) {
            $pendingItems[] = $row;
        }

        $selectRegularUser = "SELECT
                id,
                first_name,
                last_name,
                email,
                role,
                status,
                created_at
            FROM users
            WHERE role = 'REGULAR'
              AND status = 'ACTIVE'
            ORDER BY first_name, last_name";

        $resultRegularUser = $conn->query($selectRegularUser);

        if (!$resultRegularUser) {
            throw new Exception('Retrieve regular users failed: ' . $conn->error);
        }

        while ($row = $resultRegularUser->fetch_assoc()) {
            $regularUsers[] = $row;
        }
    } catch (Exception $e) {
        error_log('Retrieving admin dashboard data failed: ' . $e->getMessage());
        header('Location: ../exceptions/internalServerError.php');
        exit();
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
?>
