<?php
    require_once '../services/session.php';
    $currentRole = $_SESSION['role'];

    function filter($data) {
        return htmlspecialchars((string) $data, ENT_QUOTES, 'utf-8');
    }

    function flashMessage(): string {
        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';

        $successMessages = [
            'product_pending' => 'Product request sent. It will be added to the total quantity after admin approval.',
            'product_submitted' => 'Product request sent. It will be added to the total quantity after admin approval.',
            'product_updated' => 'Product request updated.',
            'product_deleted' => 'Product request deleted.',
            'admin_added' => 'Admin account added successfully.',
            'admin_promoted' => 'Regular user has been added as an admin.',
            'admin_removed' => 'Admin account has been archived.',
            'password_reset' => 'Admin password has been reset.',
            'item_approved' => 'Product request approved.',
            'item_rejected' => 'Product request rejected.',
            'item_deleted' => 'Item deleted successfully.',
            'item_updated' => 'Item updated successfully.',
            'item_added' => 'Item added successfully.',
            'user_deleted' => 'User account deleted successfully.',
            'user_updated' => 'User account updated successfully.'
        ];

        $errorMessages = [
            'invalid_chocolate' => 'Please select a valid chocolate.',
            'invalid_quantity' => 'Quantity must be greater than zero.',
            'invalid_product' => 'Please select a valid product request.',
            'invalid_admin' => 'Please select a valid admin account.',
            'invalid_admin_input' => 'Please complete all admin fields correctly.',
            'email_exists' => 'That email is already registered.',
            'invalid_reset_password' => 'Please enter a valid new password.',
            'invalid_user' => 'Please select a valid user account.',
            'invalid_item' => 'Please select a valid item.',
            'unauthorized_action' => 'You are not allowed to do that action.',
            'delete_failed' => 'Delete failed. Please try again.',
            'update_failed' => 'Update failed. Please try again.',
            'add_failed' => 'Add failed. Please try again.'
        ];

        if (isset($successMessages[$success])) {
            return $successMessages[$success];
        }

        if (isset($errorMessages[$error])) {
            return $errorMessages[$error];
        }

        if ($success !== '') {
            return ucfirst(str_replace('_', ' ', $success)) . '.';
        }

        if ($error !== '') {
            return 'Error: ' . ucfirst(str_replace('_', ' ', $error)) . '.';
        }

        return '';
    }

    function flashType(): string {
        if (isset($_GET['success'])) {
            return 'success';
        }

        if (isset($_GET['error'])) {
            return 'error';
        }

        return '';
    }

    function getInitials(string $firstName, string $lastName): string {
        $firstInitial = $firstName !== '' ? substr($firstName, 0, 1) : '';
        $lastInitial = $lastName !== '' ? substr($lastName, 0, 1) : '';
        $initials = strtoupper($firstInitial . $lastInitial);

        return $initials !== '' ? $initials : 'U';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css?<?php echo time(); ?>">
</head>
<body>

<!-- SUPER ADMIN -->
<section class="super-admin" id="superAdmin">
    <?php
        if ($currentRole === 'SUPER_ADMIN'):
            require_once '../managers/superAdmin.php';
            $message = flashMessage();
            $messageType = flashType();
    ?>
        <div class="header super-admin-header">
            <div>
                <p class="modal-kicker">Account management</p>
                <h1>Super Admin</h1>
            </div>

            <div class="header-actions">
                <button type="button" class="addButton" onclick="openAddAdminModal()">Add Admin</button>
                <form action="../operations/logout.php" method="post" class="logout-form" data-confirm-title="Confirm Logout" data-confirm-message="You will need to sign in again to access your dashboard." data-confirm-confirm-label="Logout" data-confirm-danger="true">
                    <button type="submit" class="logoutButton">Logout</button>
                </form>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <p class="flash-message flash-<?= filter($messageType) ?>" role="status" aria-live="polite" data-auto-hide="3000"><?= filter($message) ?></p>
        <?php endif; ?>

        <div class="section-title-row super-admin-title-row">
            <div>
                <h2>Admins</h2>
                <p>Reset admin passwords or archive admin accounts.</p>
            </div>
        </div>

        <div class="card-container super-admin-list">
            <?php if (isset($adminUsers) && count($adminUsers) > 0): ?>
                <?php foreach ($adminUsers as $row):
                    $adminId = filter($row['id'] ?? '');
                    $firstName = filter($row['first_name'] ?? '');
                    $lastName = filter($row['last_name'] ?? '');
                    $adminName = trim($firstName . ' ' . $lastName);
                    $adminEmail = filter($row['email'] ?? '');
                    $adminRole = filter($row['role'] ?? '');
                    $adminStatus = filter($row['status'] ?? '');
                    $adminCreatedAt = filter($row['created_at'] ?? '');
                    $adminInitials = filter(getInitials($firstName, $lastName));
                ?>
                    <div class="management-card" data-name="<?= filter(strtolower($adminName . ' ' . $adminEmail)) ?>">
                        <div class="management-main">
                            <div class="user-avatar" aria-hidden="true"><?= $adminInitials ?></div>

                            <div class="management-info">
                                <p class="management-name"><?= $adminName ?></p>
                                <p class="created-by"><?= $adminEmail ?></p>

                                <div class="management-meta">
                                    <span><?= $adminRole ?></span>
                                    <span class="status-badge status-active"><?= $adminStatus ?></span>
                                    <span>Created: <?= $adminCreatedAt ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="crud-actions management-actions">
                            <button
                                type="button"
                                class="btn btn-update"
                                data-admin-id="<?= $adminId ?>"
                                data-admin-name="<?= $adminName ?>"
                                onclick="openResetPasswordModal(this.dataset.adminId, this.dataset.adminName)">
                                Reset Password
                            </button>

                            <form action="../operations/removeAdmin.php" method="post" data-confirm-title="Remove Admin" data-confirm-message="This admin account will be archived and will no longer have admin access." data-confirm-confirm-label="Remove Admin" data-confirm-danger="true">
                                <input type="hidden" name="id" value="<?= $adminId ?>">
                                <button type="submit" class="btn btn-delete">Remove Admin</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-message">No active admins found.</p>
            <?php endif; ?>
        </div>

        <div class="section-title-row super-admin-title-row">
            <div>
                <h2>Regular Users</h2>
                <p>Add a regular user as an admin without creating a new account.</p>
            </div>
        </div>

        <div class="card-container super-admin-list">
            <?php if (isset($regularUsers) && count($regularUsers) > 0): ?>
                <?php foreach ($regularUsers as $row):
                    $regularId = filter($row['id'] ?? '');
                    $firstName = filter($row['first_name'] ?? '');
                    $lastName = filter($row['last_name'] ?? '');
                    $regularName = trim($firstName . ' ' . $lastName);
                    $regularEmail = filter($row['email'] ?? '');
                    $regularRole = filter($row['role'] ?? '');
                    $regularStatus = filter($row['status'] ?? '');
                    $regularCreatedAt = filter($row['created_at'] ?? '');
                    $regularInitials = filter(getInitials($firstName, $lastName));
                ?>
                    <div class="management-card" data-name="<?= filter(strtolower($regularName . ' ' . $regularEmail)) ?>">
                        <div class="management-main">
                            <div class="user-avatar user-avatar-muted" aria-hidden="true"><?= $regularInitials ?></div>

                            <div class="management-info">
                                <p class="management-name"><?= $regularName ?></p>
                                <p class="created-by"><?= $regularEmail ?></p>

                                <div class="management-meta">
                                    <span><?= $regularRole ?></span>
                                    <span class="status-badge status-active"><?= $regularStatus ?></span>
                                    <span>Created: <?= $regularCreatedAt ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="crud-actions management-actions">
                            <form action="../operations/addAdmin.php" method="post" data-confirm-title="Add User as Admin" data-confirm-message="This regular user will be promoted and given admin access." data-confirm-confirm-label="Add as Admin">
                                <input type="hidden" name="user_id" value="<?= $regularId ?>">
                                <button type="submit" class="btn btn-submit">Add as Admin</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-message">No active regular users found.</p>
            <?php endif; ?>
        </div>

        <!-- Add Admin Modal -->
        <section class="modal-overlay" id="addAdminModal" aria-hidden="true">
            <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="addAdminModalTitle">
                <div class="modal-header">
                    <div>
                        <p class="modal-kicker">Admin account</p>
                        <h2 id="addAdminModalTitle">Add New Admin</h2>
                    </div>
                    <button type="button" class="modal-close" onclick="closeAddAdminModal()" aria-label="Close add admin modal">&times;</button>
                </div>

                <form action="../operations/addAdmin.php" method="post" class="modal-form" data-confirm-title="Add New Admin" data-confirm-message="Create this new admin account?" data-confirm-confirm-label="Add Admin">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="adminFirstName">First Name</label>
                            <input type="text" id="adminFirstName" name="first_name" placeholder="First name" required>
                        </div>

                        <div class="form-group">
                            <label for="adminLastName">Last Name</label>
                            <input type="text" id="adminLastName" name="last_name" placeholder="Last name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="adminEmail">Email</label>
                        <input type="email" id="adminEmail" name="email" placeholder="admin@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="adminPassword">Password</label>
                        <input type="password" id="adminPassword" name="password" placeholder="Temporary password" required>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-cancel" onclick="closeAddAdminModal()">Cancel</button>
                        <button type="submit" class="btn btn-submit">Add Admin</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Reset Password Modal -->
        <section class="modal-overlay" id="resetPasswordModal" aria-hidden="true">
            <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="resetPasswordModalTitle">
                <div class="modal-header">
                    <div>
                        <p class="modal-kicker">Security</p>
                        <h2 id="resetPasswordModalTitle">Reset Password</h2>
                        <p class="created-by" id="resetPasswordAdminName"></p>
                    </div>
                    <button type="button" class="modal-close" onclick="closeResetPasswordModal()" aria-label="Close reset password modal">&times;</button>
                </div>

                <form action="../operations/resetPassword.php" method="post" class="modal-form" data-confirm-title="Reset Password" data-confirm-message="This will replace the admin password with the new password you entered." data-confirm-confirm-label="Reset Password" data-confirm-danger="true">
                    <input type="hidden" name="id" id="resetPasswordAdminId">

                    <div class="form-group">
                        <label for="newAdminPassword">New Password</label>
                        <input type="password" id="newAdminPassword" name="new_password" placeholder="New password" required>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-cancel" onclick="closeResetPasswordModal()">Cancel</button>
                        <button type="submit" class="btn btn-submit">Reset Password</button>
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>
</section>

<!-- ADMIN -->
<section class="admin" id="admin">
    <?php
        if ($currentRole === 'ADMIN'):
            require_once '../managers/admin.php';
            $message = flashMessage();
            $messageType = flashType();
    ?>
        <div class="dashboard-shell">
            <div class="header role-header">
                <div>
                    <p class="modal-kicker">Inventory dashboard</p>
                    <h1>Welcome, <?= filter($firstName) ?></h1>
                </div>
                <div class="header-actions">
                    <span class="role-pill">ADMIN</span>
                    <form action="../operations/logout.php" method="post" class="logout-form" data-confirm-title="Confirm Logout" data-confirm-message="You will need to sign in again to access your dashboard." data-confirm-confirm-label="Logout" data-confirm-danger="true">
                        <button type="submit" class="logoutButton">Logout</button>
                    </form>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <p class="flash-message flash-<?= filter($messageType) ?>" role="status" aria-live="polite" data-auto-hide="3000"><?= filter($message) ?></p>
            <?php endif; ?>

            <nav class="dashboard-nav">
                <button type="button" class="tab active" onclick="showHome()">
                    <img src="../assets/img/icon/home.png" alt="" width="20">
                    Home
                </button>
                <button type="button" class="tab" onclick="showItems()">
                    <img src="../assets/img/icon/chocolate.png" alt="" width="20">
                    Pending Items
                </button>
                <button type="button" class="tab" onclick="showUsers()">
                    <img src="../assets/img/icon/user.png" alt="" width="20">
                    Users
                </button>
            </nav>

            <!-- ADMIN HOME: grouped ACTIVE products -->
            <div class="cards admin-section dashboard-panel" id="home">
                <div class="section-title-row">
                    <div>
                        <h2>Active Products</h2>
                        <p>Same chocolates are grouped into one total quantity.</p>
                    </div>
                    <span class="count-pill"><?= isset($activeProducts) ? count($activeProducts) : 0 ?> products</span>
                </div>

                <div class="dashboard-list">
                    <?php if (isset($activeProducts) && count($activeProducts) > 0): ?>
                        <?php foreach ($activeProducts as $row):
                            $chocolateName = filter($row['chocolate_name'] ?? '');
                            $image = filter($row['image_path'] ?? '');
                            $dataName = filter(strtolower($row['chocolate_name'] ?? ''));
                            $totalQuantity = filter($row['total_quantity'] ?? '0');
                            $totalRecords = filter($row['total_records'] ?? '0');
                            $totalContributors = (int) ($row['total_contributors'] ?? 0);
                            $contributors = filter($row['contributors'] ?? '');
                            $addedByLabel = $totalContributors > 1 ? 'Multiple users' : $contributors;
                        ?>
                            <div class="inventory-card choco-card" data-name="<?= $dataName ?>">
                                <div class="inventory-main">
                                    <img src="<?= $image ?>" alt="<?= $chocolateName ?>" width="54" height="54" class="choco-image product-thumb">
                                    <div class="inventory-info">
                                        <p class="inventory-name"><?= $chocolateName ?></p>
                                        <p class="created-by">Added by: <?= $addedByLabel ?></p>
                                        <div class="inventory-tags">
                                            <span class="status-badge status-active">ACTIVE</span>
                                            <span><?= $totalContributors ?> contributor<?= $totalContributors === 1 ? '' : 's' ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="inventory-stats">
                                    <div class="stat-block">
                                        <span>Total Qty</span>
                                        <strong><?= $totalQuantity ?></strong>
                                    </div>
                                    <div class="stat-block">
                                        <span>Records</span>
                                        <strong><?= $totalRecords ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">No active chocolates found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ADMIN ITEMS: pending rows stay separate -->
            <div class="items admin-section dashboard-panel" id="items" style="display: none;">
                <div class="section-title-row">
                    <div>
                        <h2>Pending Items</h2>
                        <p>Approve or reject product requests from users.</p>
                    </div>
                    <span class="count-pill"><?= isset($pendingItems) ? count($pendingItems) : 0 ?> pending</span>
                </div>

                <div class="dashboard-list">
                    <?php if (isset($pendingItems) && count($pendingItems) > 0): ?>
                        <?php foreach ($pendingItems as $row):
                            $inventoryId = filter($row['inventory_id'] ?? '');
                            $chocolateName = filter($row['chocolate_name'] ?? '');
                            $requestBy = trim(filter($row['first_name'] ?? '') . ' ' . filter($row['last_name'] ?? ''));
                            $userRole = filter($row['role'] ?? '');
                            $requestEmail = filter($row['email'] ?? '');
                            $image = filter($row['image_path'] ?? '');
                            $quantity = filter($row['quantity'] ?? '');
                            $createdAt = filter($row['created_at'] ?? '');
                        ?>
                            <div class="inventory-card request-card">
                                <div class="inventory-main">
                                    <img src="<?= $image ?>" alt="<?= $chocolateName ?>" width="54" height="54" class="choco-image product-thumb">
                                    <div class="inventory-info">
                                        <p class="inventory-name"><?= $chocolateName ?></p>
                                        <p class="created-by">Requested by: <?= $requestBy ?><?= $requestEmail !== '' ? ' · ' . $requestEmail : '' ?></p>
                                        <div class="inventory-tags">
                                            <span class="status-badge status-pending">PENDING</span>
                                            <span><?= $userRole ?></span>
                                            <span><?= $createdAt ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="inventory-stats request-actions-wrap">
                                    <div class="stat-block">
                                        <span>Qty</span>
                                        <strong><?= $quantity ?></strong>
                                    </div>

                                    <div class="crud-actions management-actions">
                                        <a class="btn btn-update requires-confirm" href="../pages/updateItem.php?id=<?= $inventoryId ?>" data-confirm-title="Approve Request" data-confirm-message="Approve this pending product request?" data-confirm-confirm-label="Approve">Approve</a>

                                        <form action="../operations/deleteItem.php" method="post" data-confirm-title="Reject Request" data-confirm-message="This pending product request will be rejected." data-confirm-confirm-label="Reject" data-confirm-danger="true">
                                            <input type="hidden" name="id" value="<?= $inventoryId ?>">
                                            <button type="submit" class="btn btn-delete">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">No pending items found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ADMIN USERS -->
            <div class="users admin-section dashboard-panel" id="users" style="display: none;">
                <div class="section-title-row">
                    <div>
                        <h2>Regular Users</h2>
                        <p>Manage regular user accounts.</p>
                    </div>
                    <span class="count-pill"><?= isset($regularUsers) ? count($regularUsers) : 0 ?> users</span>
                </div>

                <div class="dashboard-list">
                    <?php if (isset($regularUsers) && count($regularUsers) > 0): ?>
                        <?php foreach ($regularUsers as $row):
                            $regularUserId = filter($row['id'] ?? '');
                            $userFirstName = filter($row['first_name'] ?? '');
                            $userLastName = filter($row['last_name'] ?? '');
                            $userName = trim($userFirstName . ' ' . $userLastName);
                            $userEmail = filter($row['email'] ?? '');
                            $userRole = filter($row['role'] ?? '');
                            $userStatus = filter($row['status'] ?? '');
                            $userCreatedAt = filter($row['created_at'] ?? '');
                            $userInitials = filter(getInitials($userFirstName, $userLastName));
                        ?>
                            <div class="management-card user-management-card" data-name="<?= filter(strtolower($userName . ' ' . $userEmail)) ?>">
                                <div class="management-main">
                                    <div class="user-avatar user-avatar-muted" aria-hidden="true"><?= $userInitials ?></div>

                                    <div class="management-info">
                                        <p class="management-name"><?= $userName ?></p>
                                        <p class="created-by"><?= $userEmail ?></p>

                                        <div class="management-meta">
                                            <span><?= $userRole ?></span>
                                            <span class="status-badge status-active"><?= $userStatus ?></span>
                                            <span>Created: <?= $userCreatedAt ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="crud-actions management-actions">
                                    <a class="btn btn-update" href="../pages/updateUser.php?id=<?= $regularUserId ?>">Update</a>

                                    <form action="../operations/deleteUser.php" method="post" data-confirm-title="Delete User" data-confirm-message="This user account will be deleted or archived depending on your operation logic." data-confirm-confirm-label="Delete" data-confirm-danger="true">
                                        <input type="hidden" name="id" value="<?= $regularUserId ?>">
                                        <button type="submit" class="btn btn-delete">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">No regular users found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- REGULAR USER -->
<section class="regular-user" id="regularUser">
    <?php
        if ($currentRole === 'REGULAR'):
            require_once '../managers/regularUser.php';
            $message = flashMessage();
            $messageType = flashType();
    ?>
        <div class="dashboard-shell">
            <div class="header role-header">
                <div>
                    <p class="modal-kicker">Product inventory</p>
                    <h1>Product</h1>
                </div>
                <div class="header-actions">
                    <button type="button" class="addButton" onclick="openAddProductModal()">Add Product</button>
                    <form action="../operations/logout.php" method="post" class="logout-form" data-confirm-title="Confirm Logout" data-confirm-message="You will need to sign in again to access your dashboard." data-confirm-confirm-label="Logout" data-confirm-danger="true">
                        <button type="submit" class="logoutButton">Logout</button>
                    </form>
                </div>
            </div>

            <div class="search-add regular-toolbar">
                <div class="search-container">
                    <img src="../assets/img/icon/search.png" alt="" width="20" height="20">
                    <input type="text"
                        placeholder="Search a Chocolate"
                        class="search-bar"
                        id="searchInput"
                        oninput="filterChocolates()">
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <p class="flash-message flash-<?= filter($messageType) ?>" role="status" aria-live="polite" data-auto-hide="3000"><?= filter($message) ?></p>
            <?php endif; ?>

            <div class="regular-tabs" role="tablist" aria-label="Regular user product sections">
                <button
                    type="button"
                    class="regular-tab-btn active"
                    id="regularActiveTab"
                    onclick="showRegularActive()"
                    role="tab"
                    aria-controls="regularActivePanel"
                    aria-selected="true">
                    Active Products
                    <span><?= isset($activeProducts) ? count($activeProducts) : 0 ?></span>
                </button>

                <button
                    type="button"
                    class="regular-tab-btn"
                    id="regularPendingTab"
                    onclick="showRegularPending()"
                    role="tab"
                    aria-controls="regularPendingPanel"
                    aria-selected="false">
                    Pending Requests
                    <span><?= isset($pendingProducts) ? count($pendingProducts) : 0 ?></span>
                </button>
            </div>

            <div id="noResultMessage" style="display:none;">
                <h1>No Chocolates Found!</h1>
            </div>

            <div class="regular-tab-panel" id="regularActivePanel" role="tabpanel" aria-labelledby="regularActiveTab">
                <div class="section-title-row">
                    <div>
                        <h2>Active Products</h2>
                        <p>Same chocolates are combined into one total quantity.</p>
                    </div>
                    <span class="count-pill"><?= isset($activeProducts) ? count($activeProducts) : 0 ?> products</span>
                </div>

                <div class="dashboard-list">
                    <?php if (isset($activeProducts) && count($activeProducts) > 0): ?>
                        <?php foreach ($activeProducts as $row):
                            $chocolateName = filter($row['chocolate_name'] ?? '');
                            $image = filter($row['image_path'] ?? '');
                            $dataName = filter(strtolower($row['chocolate_name'] ?? ''));
                            $totalQuantity = filter($row['total_quantity'] ?? '0');
                            $totalRecords = filter($row['total_records'] ?? '0');
                            $totalContributors = (int) ($row['total_contributors'] ?? 0);
                            $contributors = filter($row['contributors'] ?? '');
                            $addedByLabel = $totalContributors > 1 ? 'Multiple users' : $contributors;
                        ?>
                            <div class="inventory-card choco-card" data-name="<?= $dataName ?>">
                                <div class="inventory-main">
                                    <img src="<?= $image ?>" alt="<?= $chocolateName ?>" width="54" height="54" class="choco-image product-thumb">
                                    <div class="inventory-info">
                                        <p class="inventory-name"><?= $chocolateName ?></p>
                                        <p class="created-by">Added by: <?= $addedByLabel ?></p>
                                        <div class="inventory-tags">
                                            <span class="status-badge status-active">ACTIVE</span>
                                            <span><?= $totalContributors ?> contributor<?= $totalContributors === 1 ? '' : 's' ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="inventory-stats">
                                    <div class="stat-block">
                                        <span>Total Qty</span>
                                        <strong><?= $totalQuantity ?></strong>
                                    </div>
                                    <div class="stat-block">
                                        <span>Records</span>
                                        <strong><?= $totalRecords ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">No active chocolates found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="regular-tab-panel is-hidden" id="regularPendingPanel" role="tabpanel" aria-labelledby="regularPendingTab" hidden>
                <div class="section-title-row">
                    <div>
                        <h2>My Pending Requests</h2>
                        <p>You can update or delete only your own pending requests.</p>
                    </div>
                    <span class="count-pill"><?= isset($pendingProducts) ? count($pendingProducts) : 0 ?> pending</span>
                </div>

                <div class="dashboard-list pending-list">
                    <?php if (isset($pendingProducts) && count($pendingProducts) > 0): ?>
                        <?php foreach ($pendingProducts as $row):
                            $inventoryId = filter($row['inventory_id'] ?? '');
                            $currentChocolateId = filter($row['chocolate_id'] ?? '');
                            $chocolateName = filter($row['chocolate_name'] ?? '');
                            $image = filter($row['image_path'] ?? '');
                            $quantity = filter($row['quantity'] ?? '');
                            $createdAt = filter($row['created_at'] ?? '');
                        ?>
                            <div class="inventory-card choco-card pending-request-card" data-name="<?= filter(strtolower($row['chocolate_name'] ?? '')) ?>">
                                <div class="inventory-main">
                                    <img src="<?= $image ?>" alt="<?= $chocolateName ?>" width="54" height="54" class="choco-image product-thumb">
                                    <div class="inventory-info">
                                        <p class="inventory-name"><?= $chocolateName ?></p>
                                        <p class="created-by">Submitted: <?= $createdAt ?></p>
                                        <div class="inventory-tags">
                                            <span class="status-badge status-pending">PENDING</span>
                                            <span>Waiting for admin approval</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="inventory-stats request-actions-wrap">
                                    <div class="stat-block">
                                        <span>Qty</span>
                                        <strong><?= $quantity ?></strong>
                                    </div>

                                    <div class="crud-actions management-actions">
                                        <button
                                            type="button"
                                            class="btn btn-update"
                                            data-inventory-id="<?= $inventoryId ?>"
                                            data-chocolate-id="<?= $currentChocolateId ?>"
                                            data-quantity="<?= $quantity ?>"
                                            data-chocolate-name="<?= $chocolateName ?>"
                                            onclick="openUpdateProductModalFromButton(this)">
                                            Update
                                        </button>

                                        <form action="../operations/deleteProduct.php" method="post" data-confirm-title="Delete Pending Request" data-confirm-message="This pending product request will be deleted." data-confirm-confirm-label="Delete" data-confirm-danger="true">
                                            <input type="hidden" name="id" value="<?= $inventoryId ?>">
                                            <button type="submit" class="btn btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">No pending requests.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Product Modal -->
            <section class="modal-overlay" id="addProductModal" aria-hidden="true">
                <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="addProductModalTitle">
                    <div class="modal-header">
                        <div>
                            <p class="modal-kicker">Product request</p>
                            <h2 id="addProductModalTitle">Add Product</h2>
                        </div>
                        <button type="button" class="modal-close" onclick="closeAddProductModal()" aria-label="Close add product modal">&times;</button>
                    </div>

                    <form action="../operations/addProduct.php" method="post" class="modal-form" data-confirm-title="Submit Product Request" data-confirm-message="Submit this product request for admin approval?" data-confirm-confirm-label="Submit Request">
                        <div class="form-group">
                            <label for="chocolateItem">Chocolate</label>
                            <select name="chocolateItem" id="chocolateItem" required>
                                <option value="" disabled selected>Select chocolate</option>
                                <?php foreach ($chocolateOptions as $chocolate):
                                    $optionId = filter($chocolate['id'] ?? '');
                                    $optionName = filter($chocolate['chocolate_name'] ?? '');
                                ?>
                                    <option value="<?= $optionId ?>"><?= $optionName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="chocolateQuantity">Quantity</label>
                            <input type="number" id="chocolateQuantity" name="chocolateQuantity" min="1" placeholder="Enter quantity" required>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-cancel" onclick="closeAddProductModal()">Cancel</button>
                            <button type="submit" class="btn btn-submit">Submit Request</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Update Pending Product Modal -->
            <section class="modal-overlay" id="updateProductModal" aria-hidden="true">
                <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="updateProductModalTitle">
                    <div class="modal-header">
                        <div>
                            <p class="modal-kicker">Pending request</p>
                            <h2 id="updateProductModalTitle">Update Product Request</h2>
                            <p class="created-by" id="updateProductName"></p>
                        </div>
                        <button type="button" class="modal-close" onclick="closeUpdateProductModal()" aria-label="Close update product modal">&times;</button>
                    </div>

                    <form action="../operations/updateProduct.php" method="post" class="modal-form" data-confirm-title="Update Product Request" data-confirm-message="Save the changes to this pending product request?" data-confirm-confirm-label="Save Changes">
                        <input type="hidden" name="id" id="updateProductId">

                        <div class="form-group">
                            <label for="updateChocolateItem">Chocolate</label>
                            <select name="chocolateItem" id="updateChocolateItem" required>
                                <?php foreach ($chocolateOptions as $chocolate):
                                    $optionId = filter($chocolate['id'] ?? '');
                                    $optionName = filter($chocolate['chocolate_name'] ?? '');
                                ?>
                                    <option value="<?= $optionId ?>"><?= $optionName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="updateChocolateQuantity">Quantity</label>
                            <input type="number" id="updateChocolateQuantity" name="quantity" min="1" placeholder="Enter quantity" required>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-cancel" onclick="closeUpdateProductModal()">Cancel</button>
                            <button type="submit" class="btn btn-submit">Save Changes</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    <?php endif; ?>
</section>


<!-- Shared Confirmation Modal -->
<section class="modal-overlay" id="confirmActionModal" aria-hidden="true">
    <div class="addItemModal confirmation-modal" role="dialog" aria-modal="true" aria-labelledby="confirmActionTitle">
        <div class="modal-header">
            <div>
                <p class="modal-kicker" id="confirmActionKicker">Confirmation</p>
                <h2 id="confirmActionTitle">Confirm Action</h2>
            </div>
            <button type="button" class="modal-close" onclick="closeConfirmActionModal()" aria-label="Close confirmation modal">&times;</button>
        </div>

        <div class="confirm-body">
            <div class="confirm-icon" id="confirmActionIcon" aria-hidden="true">!</div>
            <div>
                <p class="confirm-title" id="confirmActionMessage">Are you sure?</p>
                <p class="confirm-text" id="confirmActionSubtext">This action will continue after you confirm.</p>
            </div>
        </div>

        <div class="modal-actions confirmation-actions">
            <button type="button" class="btn btn-cancel" onclick="closeConfirmActionModal()">Cancel</button>
            <button type="button" class="btn btn-submit" id="confirmActionButton" onclick="confirmPendingAction()">Confirm</button>
        </div>
    </div>
</section>

<script src="../assets/js/script.js?<?php echo time(); ?>"></script>
</body>
</html>
