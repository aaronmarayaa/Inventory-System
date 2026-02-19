<?php
    require_once '../services/session.php';
    $role = $_SESSION["role"];

    function filter($data) {
        return htmlspecialchars($data, ENT_QUOTES, "utf-8");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<!-- REGULAR USER -->
<section class="regular-user">
    <?php 
        if ($role === 'REGULAR'):
            require "../managers/regularUser.php";
    ?>
        <h1>Welcome, <?= htmlspecialchars($firstName) ?></h1>
        <hr>
        <div class="search-add">
            <input type="text" 
            placeholder="Search a Chocolate" 
            class="search-bar" 
            id="searchInput"
            oninput="filterChocolates()">
            <input type="button" value="Add Product">
        </div>
        <div id="noResultMessage" style="display:none;">
            <h1>No Chocolates Found!</h1>
        </div>
        <div class="cards">
            <?php while ($row = $result->fetch_assoc()): 
                $chocolateName = filter($row["chocolate_name"]);
                $image = filter($row["image_path"]);
                $dataName = strtolower($chocolateName);
            ?>
                <div class="choco-card" data-name="<?= $dataName ?>">
                    <img src=<?= $image ?> alt="<?= $chocolateName ?>" width="50" height="50">
                    <p><?= $chocolateName ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<section class="modal" id="modal">
    <?php 
        require "../lib/connection.php";

        $sqlStatement = "SELECT id, chocolate_name from chocolate_items";
        $result = $conn->query($sqlStatement);
    ?>
    <div class="addItemModal">
        <h1>Add New Chocolate</h1>
        <form action="../operations/addItem.php" method="post">
            <select name="chocolateItem" id="chocolateItem" required>
                <?php while($row = $result->fetch_assoc()): 
                    $chocolateName = filter($row["chocolate_name"]);
                    $chocolateItem = filter($row["id"]);
                ?>
                    <option value="<?= $chocolateItem ?>">
                        <?= $chocolateName ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <div>
                <label for="chocolateQuantity">Quantity: </label>
                <input type="number" id="chocolateQuantity" name="chocolateQuantity">
            </div>

            <input type="submit" value="Add">
        </form>
    </div>
</section>
</body>
<script src="../assets/js/script.js"></script>
</html>