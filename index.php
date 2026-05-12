<?php
    session_start();

    require_once __DIR__ . '/security/csrf.php';

    if (!empty($_SESSION['loginSuccess']) && $_SESSION['loginSuccess'] === true) {
        header('Location: pages/home.php');
        exit;
    }

    function filter($data) {
        return htmlspecialchars((string) $data, ENT_QUOTES, 'utf-8');
    }

    $successMessages = [
        'registered' => 'Account created successfully. You can now log in.',
    ];

    $errorMessages = [
        'invalid_credentials' => 'Invalid email or password.',
        'invalid' => 'Invalid email or password.',
        'empty' => 'Please enter your email and password.',
        'inactive' => 'This account is inactive. Please contact the super admin.',
        'unauthorized' => 'Please log in first.',
        'forbidden' => 'You do not have permission to access that page.',
    ];

    $flashMessage = '';
    $flashClass = '';
    $loginError = '';

    if (isset($_SESSION['login_error'])) {
        $loginError = $_SESSION['login_error'];
        unset($_SESSION['login_error']);
    } elseif (isset($_GET['error'], $errorMessages[$_GET['error']])) {
        $loginError = $errorMessages[$_GET['error']];
    }

    if (($_GET['logout'] ?? '') === 'success') {
        $flashMessage = 'Logged out successfully.';
        $flashClass = 'flash-success';
    } elseif (isset($_GET['success'], $successMessages[$_GET['success']])) {
        $flashMessage = $successMessages[$_GET['success']];
        $flashClass = 'flash-success';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chocolate Inventory | Login</title>
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css?<?php echo time(); ?>">
</head>
<body class="login-page">
    <?php if ($flashMessage !== ''): ?>
        <div class="flash-message <?= $flashClass ?>" data-auto-hide="3000">
            <?= filter($flashMessage) ?>
        </div>
    <?php endif; ?>

    <main class="login-shell" aria-label="Chocolate Inventory Login">
        <section class="login-brand-card" aria-label="System information">
            <p class="modal-kicker">Inventory System</p>
            <h1>Chocolate Inventory</h1>
            <p class="login-brand-text">
                Track active products, pending requests, admin approvals, and user-managed stock in one clean dashboard.
            </p>
        </section>

        <section class="login-card" aria-label="Login form">
            <div class="login-card-header">
                <p class="modal-kicker">Welcome back</p>
                <h2>Log in to your account</h2>
                <p>Use your registered email and password to continue.</p>
            </div>

            <form method="POST" action="services/login_process.php" class="login-form">
                <?= csrfInput(); ?>

                <?php if ($loginError !== ''): ?>
                    <div class="login-input-error" role="alert" style="color: red">
                        <?= filter($loginError) ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@email.com"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter password"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="password-toggle" onclick="toggleLoginPassword()" aria-label="Show or hide password">
                            Show
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-submit-btn">Login</button>
            </form>
        </section>
    </main>

    <script src="assets/js/script.js?<?php echo time(); ?>"></script>
</body>
</html>