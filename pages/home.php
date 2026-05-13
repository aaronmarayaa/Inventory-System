<?php
    require_once '../services/session.php';
    require_once '../security/csrf.php';
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
            'item_archived' => 'Item archived successfully.',
            'item_restored' => 'Archived item restored successfully.',
            'user_added' => 'User account added successfully.',
            'user_archived' => 'User account archived successfully.',
            'user_password_reset' => 'User password has been reset.',
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
            'invalid_user_input' => 'Please complete all user fields correctly.',
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
                    <?= csrfInput(); ?>
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
                                <?= csrfInput(); ?>
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
                                <?= csrfInput(); ?>
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
                    <?= csrfInput(); ?>
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
                    <?= csrfInput(); ?>
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
                    <button type="button" class="addButton" onclick="openAdminAddItemModal()">Add Item</button>
                    <button type="button" class="addButton" onclick="openAdminAddUserModal()">Add User</button>
                    <span class="role-pill">ADMIN</span>
                    <form action="../operations/logout.php" method="post" class="logout-form" data-confirm-title="Confirm Logout" data-confirm-message="You will need to sign in again to access your dashboard." data-confirm-confirm-label="Logout" data-confirm-danger="true">
                        <?= csrfInput(); ?>
                        <button type="submit" class="logoutButton">Logout</button>
                    </form>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <p class="flash-message flash-<?= filter($messageType) ?>" role="status" aria-live="polite" data-auto-hide="3000"><?= filter($message) ?></p>
            <?php endif; ?>

            <nav class="dashboard-nav admin-dashboard-nav">
                <button type="button" class="tab active" onclick="showAdminSection('home', this)">
                    <img src="../assets/img/icon/home.png" alt="" width="20">
                    Home
                </button>
                <button type="button" class="tab" onclick="showAdminSection('items', this)">
                    <img src="../assets/img/icon/chocolate.png" alt="" width="20">
                    Pending Items
                </button>
                <button type="button" class="tab" onclick="showAdminSection('users', this)">
                    <img src="../assets/img/icon/user.png" alt="" width="20">
                    Users
                </button>
                <button type="button" class="tab" onclick="showAdminSection('archives', this)">
                    Archived Items
                </button>
            </nav>

            <!-- ADMIN HOME: grouped ACTIVE products and individual ACTIVE inventory records -->
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

                <div class="section-title-row" style="margin-top: 24px;">
                    <div>
                        <h2>Item Records</h2>
                        <p>View, update, or archive individual active inventory records.</p>
                    </div>
                    <span class="count-pill"><?= isset($activeInventoryItems) ? count($activeInventoryItems) : 0 ?> records</span>
                </div>

                <div class="dashboard-list">
                    <?php if (isset($activeInventoryItems) && count($activeInventoryItems) > 0): ?>
                        <?php foreach ($activeInventoryItems as $row):
                            $inventoryId = filter($row['inventory_id'] ?? '');
                            $chocolateId = filter($row['chocolate_id'] ?? '');
                            $chocolateName = filter($row['chocolate_name'] ?? '');
                            $image = filter($row['image_path'] ?? '');
                            $quantity = filter($row['quantity'] ?? '');
                            $createdAt = filter($row['created_at'] ?? '');
                            $createdBy = trim(filter($row['first_name'] ?? '') . ' ' . filter($row['last_name'] ?? ''));
                            $createdByEmail = filter($row['email'] ?? '');
                            $createdByRole = filter($row['role'] ?? '');
                        ?>
                            <div class="inventory-card request-card" data-name="<?= filter(strtolower($chocolateName . ' ' . $createdBy . ' ' . $createdByEmail)) ?>">
                                <div class="inventory-main">
                                    <img src="<?= $image ?>" alt="<?= $chocolateName ?>" width="54" height="54" class="choco-image product-thumb">
                                    <div class="inventory-info">
                                        <p class="inventory-name"><?= $chocolateName ?></p>
                                        <p class="created-by">Created by: <?= $createdBy ?><?= $createdByEmail !== '' ? ' · ' . $createdByEmail : '' ?></p>
                                        <div class="inventory-tags">
                                            <span class="status-badge status-active">ACTIVE</span>
                                            <span><?= $createdByRole ?></span>
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
                                        <button
                                            type="button"
                                            class="btn btn-update"
                                            data-inventory-id="<?= $inventoryId ?>"
                                            data-chocolate-id="<?= $chocolateId ?>"
                                            data-chocolate-name="<?= $chocolateName ?>"
                                            data-quantity="<?= $quantity ?>"
                                            data-created-by="<?= $createdBy ?>"
                                            data-created-by-email="<?= $createdByEmail ?>"
                                            data-created-by-role="<?= $createdByRole ?>"
                                            data-created-at="<?= $createdAt ?>"
                                            onclick="openAdminViewItemModal(this)">
                                            View
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-update"
                                            data-inventory-id="<?= $inventoryId ?>"
                                            data-chocolate-id="<?= $chocolateId ?>"
                                            data-chocolate-name="<?= $chocolateName ?>"
                                            data-quantity="<?= $quantity ?>"
                                            onclick="openAdminUpdateItemModal(this)">
                                            Update
                                        </button>

                                        <form action="../operations/deleteProduct.php" method="post" data-confirm-title="Archive Item" data-confirm-message="This active item will be moved to archived items." data-confirm-confirm-label="Archive" data-confirm-danger="true">
                                            <?= csrfInput(); ?>
                                            <input type="hidden" name="id" value="<?= $inventoryId ?>">
                                            <button type="submit" class="btn btn-delete">Archive</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">No active item records found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ADMIN ITEMS: pending rows stay separate -->
            <div class="admin-items-panel admin-section dashboard-panel" id="items" style="display: none;">
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
                                        <form action="../operations/approveProduct.php" method="post" data-confirm-title="Approve Request" data-confirm-message="Approve this pending product request? It will be added to the active inventory total." data-confirm-confirm-label="Approve">
                                            <?= csrfInput(); ?>
                                            <input type="hidden" name="id" value="<?= $inventoryId ?>">
                                            <button type="submit" class="btn btn-submit">Approve</button>
                                        </form>

                                        <form action="../operations/deleteProduct.php" method="post" data-confirm-title="Reject Request" data-confirm-message="This pending product request will be archived." data-confirm-confirm-label="Reject" data-confirm-danger="true">
                                            <?= csrfInput(); ?>
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
            <div class="admin-users-panel admin-section dashboard-panel" id="users" style="display: none;">
                <div class="section-title-row">
                    <div>
                        <h2>Regular Users</h2>
                        <p>Add users, archive users, or reset regular user passwords.</p>
                    </div>
                    <button type="button" class="addButton" onclick="openAdminAddUserModal()">Add User</button>
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
                                    <button
                                        type="button"
                                        class="btn btn-update"
                                        data-user-id="<?= $regularUserId ?>"
                                        data-user-name="<?= $userName ?>"
                                        onclick="openAdminResetUserPasswordModal(this.dataset.userId, this.dataset.userName)">
                                        Reset Password
                                    </button>

                                    <form action="../operations/deleteUser.php" method="post" data-confirm-title="Archive User" data-confirm-message="This regular user account will be archived and can no longer log in." data-confirm-confirm-label="Archive User" data-confirm-danger="true">
                                        <?= csrfInput(); ?>
                                        <input type="hidden" name="id" value="<?= $regularUserId ?>">
                                        <button type="submit" class="btn btn-delete">Archive User</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">No regular users found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ADMIN ARCHIVED ITEMS -->
            <div class="archives admin-section dashboard-panel" id="archives" style="display: none;">
                <div class="section-title-row">
                    <div>
                        <h2>Archived Items</h2>
                        <p>Restore archived inventory records back to active inventory.</p>
                    </div>
                    <span class="count-pill"><?= isset($archivedItems) ? count($archivedItems) : 0 ?> archived</span>
                </div>

                <div class="dashboard-list">
                    <?php if (isset($archivedItems) && count($archivedItems) > 0): ?>
                        <?php foreach ($archivedItems as $row):
                            $inventoryId = filter($row['inventory_id'] ?? '');
                            $chocolateName = filter($row['chocolate_name'] ?? '');
                            $image = filter($row['image_path'] ?? '');
                            $quantity = filter($row['quantity'] ?? '');
                            $createdAt = filter($row['created_at'] ?? '');
                            $createdBy = trim(filter($row['first_name'] ?? '') . ' ' . filter($row['last_name'] ?? ''));
                            $createdByEmail = filter($row['email'] ?? '');
                            $createdByRole = filter($row['role'] ?? '');
                        ?>
                            <div class="inventory-card request-card" data-name="<?= filter(strtolower($chocolateName . ' ' . $createdBy . ' ' . $createdByEmail)) ?>">
                                <div class="inventory-main">
                                    <img src="<?= $image ?>" alt="<?= $chocolateName ?>" width="54" height="54" class="choco-image product-thumb">
                                    <div class="inventory-info">
                                        <p class="inventory-name"><?= $chocolateName ?></p>
                                        <p class="created-by">Created by: <?= $createdBy ?><?= $createdByEmail !== '' ? ' · ' . $createdByEmail : '' ?></p>
                                        <div class="inventory-tags">
                                            <span class="status-badge status-archived">ARCHIVED</span>
                                            <span><?= $createdByRole ?></span>
                                            <span><?= $createdAt ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="inventory-stats request-actions-wrap">
                                    <div class="stat-block">
                                        <span>Qty</span>
                                        <strong><?= $quantity ?></strong>
                                    </div>

                                    <form action="../operations/restoreProduct.php" method="post" class="crud-actions management-actions" data-confirm-title="Restore Item" data-confirm-message="Restore this archived item to active inventory?" data-confirm-confirm-label="Restore">
                                        <?= csrfInput(); ?>
                                        <input type="hidden" name="id" value="<?= $inventoryId ?>">
                                        <button type="submit" class="btn btn-submit">Restore</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">No archived items found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Item Modal -->
            <section class="modal-overlay" id="adminAddItemModal" aria-hidden="true">
                <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="adminAddItemModalTitle">
                    <div class="modal-header">
                        <div>
                            <p class="modal-kicker">Admin operation</p>
                            <h2 id="adminAddItemModalTitle">Add Item</h2>
                        </div>
                        <button type="button" class="modal-close" onclick="closeAdminAddItemModal()" aria-label="Close add item modal">&times;</button>
                    </div>

                    <form action="../operations/addProduct.php" method="post" class="modal-form" data-confirm-title="Add Item" data-confirm-message="Add this item directly to active inventory?" data-confirm-confirm-label="Add Item">
                        <?= csrfInput(); ?>
                        <div class="form-group">
                            <label for="adminAddChocolateItem">Chocolate</label>
                            <select name="chocolateItem" id="adminAddChocolateItem" required>
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
                            <label for="adminAddItemQuantity">Quantity</label>
                            <input type="number" id="adminAddItemQuantity" name="quantity" min="1" placeholder="Enter quantity" required>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-cancel" onclick="closeAdminAddItemModal()">Cancel</button>
                            <button type="submit" class="btn btn-submit">Add Item</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Update Item Modal -->
            <section class="modal-overlay" id="adminUpdateItemModal" aria-hidden="true">
                <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="adminUpdateItemModalTitle">
                    <div class="modal-header">
                        <div>
                            <p class="modal-kicker">Admin operation</p>
                            <h2 id="adminUpdateItemModalTitle">Update Item</h2>
                            <p class="created-by" id="adminUpdateItemName"></p>
                        </div>
                        <button type="button" class="modal-close" onclick="closeAdminUpdateItemModal()" aria-label="Close update item modal">&times;</button>
                    </div>

                    <form action="../operations/updateProduct.php" method="post" class="modal-form" data-confirm-title="Update Item" data-confirm-message="Save changes to this active inventory item?" data-confirm-confirm-label="Save Changes">
                        <?= csrfInput(); ?>
                        <input type="hidden" name="id" id="adminUpdateItemId">

                        <div class="form-group">
                            <label for="adminUpdateChocolateItem">Chocolate</label>
                            <select name="chocolateItem" id="adminUpdateChocolateItem" required>
                                <?php foreach ($chocolateOptions as $chocolate):
                                    $optionId = filter($chocolate['id'] ?? '');
                                    $optionName = filter($chocolate['chocolate_name'] ?? '');
                                ?>
                                    <option value="<?= $optionId ?>"><?= $optionName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="adminUpdateItemQuantity">Quantity</label>
                            <input type="number" id="adminUpdateItemQuantity" name="quantity" min="1" placeholder="Enter quantity" required>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-cancel" onclick="closeAdminUpdateItemModal()">Cancel</button>
                            <button type="submit" class="btn btn-submit">Save Changes</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- View Item Modal -->
            <section class="modal-overlay" id="adminViewItemModal" aria-hidden="true">
                <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="adminViewItemModalTitle">
                    <div class="modal-header">
                        <div>
                            <p class="modal-kicker">Item details</p>
                            <h2 id="adminViewItemModalTitle">View Item</h2>
                        </div>
                        <button type="button" class="modal-close" onclick="closeAdminViewItemModal()" aria-label="Close view item modal">&times;</button>
                    </div>

                    <div class="confirm-body">
                        <div class="confirm-icon" aria-hidden="true">i</div>
                        <div>
                            <p class="confirm-title" id="adminViewItemName">Chocolate item</p>
                            <p class="confirm-text" id="adminViewItemDetails">Item details will appear here.</p>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-submit" onclick="closeAdminViewItemModal()">Close</button>
                    </div>
                </div>
            </section>

            <!-- Add User Modal -->
            <section class="modal-overlay" id="adminAddUserModal" aria-hidden="true">
                <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="adminAddUserModalTitle">
                    <div class="modal-header">
                        <div>
                            <p class="modal-kicker">User account</p>
                            <h2 id="adminAddUserModalTitle">Add User</h2>
                        </div>
                        <button type="button" class="modal-close" onclick="closeAdminAddUserModal()" aria-label="Close add user modal">&times;</button>
                    </div>

                    <form action="../operations/addUser.php" method="post" class="modal-form" data-confirm-title="Add User" data-confirm-message="Create this regular user account?" data-confirm-confirm-label="Add User">
                        <?= csrfInput(); ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="adminUserFirstName">First Name</label>
                                <input type="text" id="adminUserFirstName" name="first_name" placeholder="First name" required>
                            </div>

                            <div class="form-group">
                                <label for="adminUserLastName">Last Name</label>
                                <input type="text" id="adminUserLastName" name="last_name" placeholder="Last name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="adminUserEmail">Email</label>
                            <input type="email" id="adminUserEmail" name="email" placeholder="user@example.com" required>
                        </div>

                        <div class="form-group">
                            <label for="adminUserPassword">Password</label>
                            <input type="password" id="adminUserPassword" name="password" placeholder="Temporary password" required>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-cancel" onclick="closeAdminAddUserModal()">Cancel</button>
                            <button type="submit" class="btn btn-submit">Add User</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Reset User Password Modal -->
            <section class="modal-overlay" id="adminResetUserPasswordModal" aria-hidden="true">
                <div class="addItemModal" role="dialog" aria-modal="true" aria-labelledby="adminResetUserPasswordModalTitle">
                    <div class="modal-header">
                        <div>
                            <p class="modal-kicker">User security</p>
                            <h2 id="adminResetUserPasswordModalTitle">Reset User Password</h2>
                            <p class="created-by" id="adminResetUserPasswordName"></p>
                        </div>
                        <button type="button" class="modal-close" onclick="closeAdminResetUserPasswordModal()" aria-label="Close reset user password modal">&times;</button>
                    </div>

                    <form action="../operations/resetPassword.php" method="post" class="modal-form" data-confirm-title="Reset User Password" data-confirm-message="This will replace the regular user's password." data-confirm-confirm-label="Reset Password" data-confirm-danger="true">
                        <?= csrfInput(); ?>
                        <input type="hidden" name="id" id="adminResetUserPasswordId">

                        <div class="form-group">
                            <label for="adminNewUserPassword">New Password</label>
                            <input type="password" id="adminNewUserPassword" name="new_password" placeholder="New password" required>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-cancel" onclick="closeAdminResetUserPasswordModal()">Cancel</button>
                            <button type="submit" class="btn btn-submit">Reset Password</button>
                        </div>
                    </form>
                </div>
            </section>
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
                        <?= csrfInput(); ?>
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
                        <p>Your added products wait here until an admin approves them.</p>
                    </div>
                    <span class="count-pill"><?= isset($pendingProducts) ? count($pendingProducts) : 0 ?> pending</span>
                </div>

                <div class="dashboard-list pending-list">
                    <?php if (isset($pendingProducts) && count($pendingProducts) > 0): ?>
                        <?php foreach ($pendingProducts as $row):
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

                                    <div class="inventory-tags">
                                        <span>Admin approval required</span>
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
                        <?= csrfInput(); ?>
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


<script>
    let pendingActionForm = null;

    function closeConfirmActionModal() {
        pendingActionForm = null;
        closeModalById('confirmActionModal');
    }

    function openConfirmActionModal(form) {
        pendingActionForm = form;

        const title = document.getElementById('confirmActionTitle');
        const message = document.getElementById('confirmActionMessage');
        const subtext = document.getElementById('confirmActionSubtext');
        const button = document.getElementById('confirmActionButton');
        const icon = document.getElementById('confirmActionIcon');

        if (title) title.textContent = form.dataset.confirmTitle || 'Confirm Action';
        if (message) message.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue?';
        if (subtext) subtext.textContent = 'This action will continue after you confirm.';
        if (button) button.textContent = form.dataset.confirmConfirmLabel || 'Confirm';

        if (button) {
            button.classList.toggle('btn-delete', form.dataset.confirmDanger === 'true');
            button.classList.toggle('btn-submit', form.dataset.confirmDanger !== 'true');
        }

        if (icon) {
            icon.textContent = form.dataset.confirmDanger === 'true' ? '!' : '✓';
        }

        openModalById('confirmActionModal');
    }

    function confirmPendingAction() {
        if (!pendingActionForm) return;

        const formToSubmit = pendingActionForm;
        pendingActionForm = null;
        closeModalById('confirmActionModal');

        formToSubmit.dataset.confirmed = 'true';

        if (typeof formToSubmit.requestSubmit === 'function') {
            formToSubmit.requestSubmit();
        } else {
            formToSubmit.submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-confirm-title]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();
                openConfirmActionModal(form);
            });
        });

        document.querySelectorAll('[data-auto-hide]').forEach(function (element) {
            const delay = parseInt(element.dataset.autoHide || '3000', 10);
            setTimeout(function () {
                element.style.display = 'none';
            }, delay);
        });
    });

    function showAdminSection(sectionId, button) {
        const adminSection = document.getElementById('admin');
        if (!adminSection) return;

        const sections = adminSection.querySelectorAll('.admin-section');
        sections.forEach((section) => {
            section.style.display = section.id === sectionId ? 'block' : 'none';
        });

        const tabs = adminSection.querySelectorAll('.admin-dashboard-nav .tab');
        tabs.forEach((tab) => tab.classList.remove('active'));

        if (button) {
            button.classList.add('active');
        }
    }

    function openModalById(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.add('active');
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModalById(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.remove('active');
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    }

    function openAdminAddItemModal() {
        openModalById('adminAddItemModal');
    }

    function closeAdminAddItemModal() {
        closeModalById('adminAddItemModal');
    }

    function openAdminUpdateItemModal(button) {
        const itemId = document.getElementById('adminUpdateItemId');
        const itemName = document.getElementById('adminUpdateItemName');
        const itemSelect = document.getElementById('adminUpdateChocolateItem');
        const itemQuantity = document.getElementById('adminUpdateItemQuantity');

        if (itemId) itemId.value = button.dataset.inventoryId || '';
        if (itemName) itemName.textContent = button.dataset.chocolateName || '';
        if (itemSelect) itemSelect.value = button.dataset.chocolateId || '';
        if (itemQuantity) itemQuantity.value = button.dataset.quantity || '';

        openModalById('adminUpdateItemModal');
    }

    function closeAdminUpdateItemModal() {
        closeModalById('adminUpdateItemModal');
    }

    function openAdminViewItemModal(button) {
        const itemName = document.getElementById('adminViewItemName');
        const itemDetails = document.getElementById('adminViewItemDetails');

        const name = button.dataset.chocolateName || 'Chocolate item';
        const quantity = button.dataset.quantity || '0';
        const createdBy = button.dataset.createdBy || 'Unknown user';
        const createdByEmail = button.dataset.createdByEmail || '';
        const createdByRole = button.dataset.createdByRole || '';
        const createdAt = button.dataset.createdAt || '';

        if (itemName) itemName.textContent = name;
        if (itemDetails) {
            itemDetails.innerHTML =
                'Quantity: <strong>' + quantity + '</strong><br>' +
                'Created by: ' + createdBy + (createdByEmail ? ' · ' + createdByEmail : '') + '<br>' +
                'Role: ' + createdByRole + '<br>' +
                'Created at: ' + createdAt;
        }

        openModalById('adminViewItemModal');
    }

    function closeAdminViewItemModal() {
        closeModalById('adminViewItemModal');
    }

    function openAdminAddUserModal() {
        openModalById('adminAddUserModal');
    }

    function closeAdminAddUserModal() {
        closeModalById('adminAddUserModal');
    }

    function openAdminResetUserPasswordModal(userId, userName) {
        const idInput = document.getElementById('adminResetUserPasswordId');
        const nameText = document.getElementById('adminResetUserPasswordName');

        if (idInput) idInput.value = userId || '';
        if (nameText) nameText.textContent = userName || '';

        openModalById('adminResetUserPasswordModal');
    }

    function closeAdminResetUserPasswordModal() {
        closeModalById('adminResetUserPasswordModal');
    }
</script>

<script src="../assets/js/script.js?<?php echo time(); ?>"></script>
</body>
</html>
