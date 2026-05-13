<?php
    http_response_code(500);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function filter($data) {
        return htmlspecialchars((string) $data, ENT_QUOTES, 'utf-8');
    }

    $rootPrefix = file_exists(__DIR__ . '/../assets/css/styles.css') ? '../' : '';
    $imagePath = $rootPrefix . 'assets/img/error-internal.png';
    $isLoggedIn = !empty($_SESSION['loginSuccess']) && $_SESSION['loginSuccess'] === true;
    $primaryLink = $isLoggedIn ? $rootPrefix . 'pages/home.php' : $rootPrefix . 'index.php';
    $primaryText = $isLoggedIn ? 'Back to dashboard' : 'Back to login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Internal Server Error | Chocolate Inventory</title>
    <link rel="stylesheet" type="text/css" href="<?= filter($rootPrefix) ?>assets/css/styles.css?<?php echo time(); ?>">
    <style>
        * {
            box-sizing: border-box;
        }

        body.error-page {
            min-height: 100vh;
            margin: 0;
            color: #2f1b12;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-color: #211f21;
        }

        .error-card {
            width: min(520px, 100%);
            padding: 28px;
            border-radius: 20px;
            background-color: #211f21;
            border: none;
            box-shadow: 0 14px 36px rgba(66, 38, 24, 0.12);
            text-align: center;
        }

        .error-image-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }

        .error-image {
            width: 180px;
            max-width: 70%;
            height: auto;
            display: block;
            border-radius: 14px;
        }

        .error-code {
            display: inline-block;
            margin-top: 10px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #fff4eb;
            color: #8a4f2a;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .error-title {
            margin: 0;
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            line-height: 1.1;
            color: white;
        }

        .error-message {
            margin: 14px auto 0;
            max-width: 420px;
            color: white;
            font-size: 0.98rem;
            line-height: 1.6;
        }

        .error-hint {
            margin: 18px auto 0;
            max-width: 420px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #fff7ef;
            border: 1px solid #ead8c8;
            color: #694837;
            font-size: 0.92rem;
            line-height: 1.5;
            text-align: left;
        }

        .error-hint strong {
            color: #5b3828;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 22px;
        }

        .error-btn {
            border: 0;
            border-radius: 12px;
            padding: 11px 16px;
            font-size: 0.94rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .error-btn:hover {
            transform: translateY(-1px);
        }

        .error-btn-primary {
            color: #fff;
            background: #5b3828;
            box-shadow: 0 10px 18px rgba(91, 56, 40, 0.18);
        }

        .error-btn-secondary {
            color: #5b3828;
            background: #fff;
            border: 1px solid #ead8c8;
        }

        @media (max-width: 520px) {
            body.error-page {
                align-items: flex-start;
                padding: 18px;
            }

            .error-card {
                padding: 22px;
            }

            .error-actions {
                flex-direction: column;
            }

            .error-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="error-page">
    <main class="error-card" aria-label="500 Internal Server Error">
        <h1 class="error-title">Something went wrong</h1>
        <span class="error-code">500 Internal Server Error</span>
        
        <div class="error-image-wrap">
            <img src="<?= filter($imagePath) ?>" alt="Chocolate cake slice" class="error-image">
        </div>

        <p class="error-message">The system had a problem while processing your request.</p>

        <div class="error-hint">
            <strong>Hint:</strong> Refresh the page after a few seconds. If the error continues, report what you were doing before this page appeared.
        </div>

        <div class="error-actions">
            <button type="button" class="error-btn error-btn-secondary" onclick="history.back()">Go back</button>
        </div>
    </main>
</body>
</html>
