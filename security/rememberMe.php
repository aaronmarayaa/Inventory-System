<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    const REMEMBER_ME_COOKIE = 'remember_me';
    const REMEMBER_ME_SECONDS = 86400 * 30;

    function rememberMeChecked(): bool {
        return isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';
    }

    function rememberMeCookieOptions(int $expires): array {
        return [
            'expires' => $expires,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax'
        ];
    }

    function clearRememberMeCookie(): void {
        setcookie(REMEMBER_ME_COOKIE, '', rememberMeCookieOptions(time() - 3600));
        unset($_COOKIE[REMEMBER_ME_COOKIE]);
    }

    function setUserSession(array $user): void {
        session_regenerate_id(true);

        $_SESSION['loginSuccess'] = true;
        $_SESSION['role'] = $user['role'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
    }

    function createRememberMeToken(mysqli $conn, int $userId): void {
        $deleteOldToken = $conn->prepare('DELETE FROM user_session WHERE user_id = ?');

        if ($deleteOldToken) {
            $deleteOldToken->bind_param('i', $userId);
            $deleteOldToken->execute();
            $deleteOldToken->close();
        }

        $selector = bin2hex(random_bytes(6));
        $validator = random_bytes(32);

        $cookieValue = $selector . ':' . bin2hex($validator);
        $tokenHash = hash('sha256', bin2hex($validator));
        $expires = date('Y-m-d H:i:s', time() + REMEMBER_ME_SECONDS);

        $stmt = $conn->prepare('INSERT INTO user_session (user_id, selector, token_hash, expires) VALUES (?, ?, ?, ?)');

        if (!$stmt) {
            error_log('Remember me insert prepare failed: ' . $conn->error);
            return;
        }

        $stmt->bind_param('isss', $userId, $selector, $tokenHash, $expires);

        if (!$stmt->execute()) {
            error_log('Remember me insert failed: ' . $stmt->error);
            $stmt->close();
            return;
        }

        $stmt->close();

        setcookie(REMEMBER_ME_COOKIE, $cookieValue, rememberMeCookieOptions(time() + REMEMBER_ME_SECONDS));
        $_COOKIE[REMEMBER_ME_COOKIE] = $cookieValue;
    }

    function deleteRememberMeToken(mysqli $conn): void {
        if (!empty($_SESSION['id'])) {
            $userId = (int) $_SESSION['id'];
            $stmt = $conn->prepare('DELETE FROM user_session WHERE user_id = ?');

            if ($stmt) {
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->close();
            }

            return;
        }

        if (empty($_COOKIE[REMEMBER_ME_COOKIE])) {
            return;
        }

        $parts = explode(':', $_COOKIE[REMEMBER_ME_COOKIE], 2);

        if (count($parts) !== 2) {
            return;
        }

        $selector = $parts[0];
        $stmt = $conn->prepare('DELETE FROM user_session WHERE selector = ?');

        if ($stmt) {
            $stmt->bind_param('s', $selector);
            $stmt->execute();
            $stmt->close();
        }
    }

    function loginFromRememberMeCookie(mysqli $conn): bool {
        if (!empty($_SESSION['loginSuccess']) && $_SESSION['loginSuccess'] === true) {
            return true;
        }

        if (empty($_COOKIE[REMEMBER_ME_COOKIE])) {
            return false;
        }

        $parts = explode(':', $_COOKIE[REMEMBER_ME_COOKIE], 2);

        if (count($parts) !== 2) {
            clearRememberMeCookie();
            return false;
        }

        [$selector, $validator] = $parts;

        if (
            strlen($selector) !== 12 ||
            strlen($validator) !== 64 ||
            !ctype_xdigit($selector) ||
            !ctype_xdigit($validator)
        ) {
            clearRememberMeCookie();
            return false;
        }

        $stmt = $conn->prepare("SELECT
                us.token_hash,
                u.id,
                u.first_name,
                u.last_name,
                u.role,
                u.status
            FROM user_session us
            JOIN users u ON us.user_id = u.id
            WHERE us.selector = ?
              AND us.expires > NOW()
            LIMIT 1");

        if (!$stmt) {
            error_log('Remember me select prepare failed: ' . $conn->error);
            clearRememberMeCookie();
            return false;
        }

        $stmt->bind_param('s', $selector);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            clearRememberMeCookie();
            return false;
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        $validatorHash = hash('sha256', $validator);

        if (!hash_equals($row['token_hash'], $validatorHash)) {
            $deleteStmt = $conn->prepare('DELETE FROM user_session WHERE selector = ?');

            if ($deleteStmt) {
                $deleteStmt->bind_param('s', $selector);
                $deleteStmt->execute();
                $deleteStmt->close();
            }

            clearRememberMeCookie();
            return false;
        }

        if ($row['status'] !== 'ACTIVE') {
            clearRememberMeCookie();
            return false;
        }

        setUserSession($row);
        return true;
    }
?>
