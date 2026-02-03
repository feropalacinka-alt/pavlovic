<?php
/**
 * 🔐 Admin Login - Protected Admin Panel Entry
 * Separate from user login/signup
 */

require_once 'inc/config.php';
require_once 'inc/db.php';

// If admin is already logged in, redirect to admin panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ' . SITE_URL . '/admin.php');
    exit();
}

// If user is logged in as regular user, tell them to logout first
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null) {
    header('Location: ' . SITE_URL . '/logout.php');
    exit();
}

$login_error = '';
$attempts = 0;

// Simple rate limiting (session-based)
if (!isset($_SESSION['admin_login_attempts'])) {
    $_SESSION['admin_login_attempts'] = 0;
    $_SESSION['admin_login_time'] = time();
}

// Reset counter after 15 minutes
if (time() - $_SESSION['admin_login_time'] > 900) {
    $_SESSION['admin_login_attempts'] = 0;
    $_SESSION['admin_login_time'] = time();
}

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    // Check rate limiting
    if ($_SESSION['admin_login_attempts'] >= 5) {
        $login_error = '⚠️ Príliš veľa pokusov. Skúste neskôr.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $login_error = 'Vyplňte meno a heslo';
            $_SESSION['admin_login_attempts']++;
        } else {
            // Check credentials
            if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_login_attempts'] = 0;
                header('Location: ' . SITE_URL . '/admin.php');
                exit();
            } else {
                $login_error = '❌ Nesprávne prihlasovacie údaje';
                $_SESSION['admin_login_attempts']++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Prihláška - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .admin-login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .admin-login-container h1 {
            text-align: center;
            color: #333;
            margin: 0 0 10px;
            font-size: 28px;
        }

        .admin-login-container .subtitle {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-info {
            background-color: #eff;
            color: #333;
            border: 1px solid #cef;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="admin-login-page">
    <div class="admin-login-container">
        <h1>🔐 Admin Panel</h1>
        <p class="subtitle">Vstup pre administrátorov</p>

        <?php if (!empty($login_error)): ?>
            <div class="alert alert-error">
                <?php echo escape($login_error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Používateľské meno:</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Heslo:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" name="admin_login" class="btn-submit">Prihlásiť sa ako Admin</button>
        </form>

        <div class="alert alert-info">
            <strong>👤 Test účet:</strong><br>
            Meno: <code>admin</code><br>
            Heslo: <code>admin123</code>
        </div>

        <div class="back-link">
            <a href="index.php">← Späť na domov</a>
        </div>
    </div>
</body>
</html>
