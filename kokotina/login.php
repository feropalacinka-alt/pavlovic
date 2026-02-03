<?php
/**
 * Login/Signup stránka pre používateľov a admin
 */

require_once 'inc/config.php';
require_once 'inc/db.php';

// Ak je user prihlásený, presmeruj na domov
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null) {
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}

// Ak je admin prihlásený, presmeruj na admin panel
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: ' . SITE_URL . '/admin.php');
    exit();
}

$login_error = '';
$signup_error = '';
$signup_success = '';

// (Admin prihlásenie presunuté mimo verejného login formulára)

// Spracovanie user prihlášky
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_login'])) {
    $email = trim($_POST['user_email'] ?? '');
    $password = $_POST['user_password'] ?? '';

    if (empty($email) || empty($password)) {
        $login_error = 'Vyplňte email a heslo';
    } else {
        // Hľadanie používateľa v databáze
        $stmt = $mysqli->prepare("SELECT id, email, password, first_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] ?? $user['email'];
                setFlashMessage('Vitajte! Ste prihlásený/á.', 'success');
                header('Location: ' . SITE_URL . '/index.php');
                exit();
            } else {
                $login_error = 'Nesprávne heslo';
            }
        } else {
            $login_error = 'Používateľ s týmto emailom neexistuje';
        }
        $stmt->close();
    }
}

// Spracovanie user registrácie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_signup'])) {
    $email = trim($_POST['reg_email'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $confirm_password = $_POST['reg_confirm'] ?? '';
    $first_name = trim($_POST['reg_first_name'] ?? '');
    $last_name = trim($_POST['reg_last_name'] ?? '');

    // Validácia
    if (empty($email)) {
        $signup_error = 'Email je povinný';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_error = 'Neplatný email';
    } elseif (strlen($password) < 6) {
        $signup_error = 'Heslo musí mať aspoň 6 znakov';
    } elseif ($password !== $confirm_password) {
        $signup_error = 'Heslá sa nezhodujú';
    } else {
        // Kontrola, či email už existuje
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $signup_error = 'Email je už zaregistrovaný';
        } else {
            // Vloženie nového používateľa
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("INSERT INTO users (email, password, first_name, last_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $email, $password_hash, $first_name, $last_name);

            if ($stmt->execute()) {
                $stmt->close();
                $signup_success = 'Registrácia úspešná! Môžete sa teraz prihlásiť.';
            } else {
                $signup_error = 'Chyba pri registrácii. Skúste neskôr.';
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
    <title>Login/Signup - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-signup-container {
            max-width: 900px;
            margin: 50px auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .login-section,
        .signup-section,
        .admin-section {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .login-section h3,
        .signup-section h3,
        .admin-section h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .admin-section h3 {
            border-bottom-color: #6c757d;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 13px;
            color: #004085;
        }

        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }

        .info-box code {
            background: #fff;
            padding: 2px 4px;
            border-radius: 2px;
            font-family: monospace;
        }

        .small-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 1024px) {
            .login-signup-container {
                grid-template-columns: 1fr 1fr;
            }

            .admin-section {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .login-signup-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo"><?php echo SITE_NAME; ?></h1>
                <nav class="nav">
                    <ul>
                        <li><a href="index.php">Domov</a></li>
                        <li><a href="cart.php">
                            Košík 
                            <span class="cart-count">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        </a></li>
                        <li><a href="login.php" class="active">Login/Signup</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">
            <a href="index.php" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-block;">← Späť na domov</a>

            <div class="login-signup-container">
                <!-- USER LOGIN SEKCIA -->
                <div class="login-section">
                    <h3>👤 Prihlásenie</h3>
                    
                    <?php if (!empty($login_error)): ?>
                        <div class="alert alert-error">
                            <?php echo escape($login_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="user_email">Email:</label>
                            <input type="email" id="user_email" name="user_email" required autofocus>
                        </div>

                        <div class="form-group">
                            <label for="user_password">Heslo:</label>
                            <input type="password" id="user_password" name="user_password" required>
                        </div>

                        <button type="submit" name="user_login" class="btn btn-primary">Prihlásiť sa</button>
                    </form>

                    <div class="info-box">
                        <strong>ℹ️ Tip:</strong>
                        Ak nemáte účet, zaregistrujte sa v sekcii vedľa.
                    </div>
                </div>

                <!-- USER SIGNUP SEKCIA -->
                <div class="signup-section">
                    <h3>📝 Registrácia</h3>
                    
                    <?php if (!empty($signup_error)): ?>
                        <div class="alert alert-error">
                            <?php echo escape($signup_error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($signup_success)): ?>
                        <div class="alert alert-success">
                            <?php echo escape($signup_success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="reg_first_name">Meno:</label>
                            <input type="text" id="reg_first_name" name="reg_first_name">
                        </div>

                        <div class="form-group">
                            <label for="reg_last_name">Priezvisko:</label>
                            <input type="text" id="reg_last_name" name="reg_last_name">
                        </div>

                        <div class="form-group">
                            <label for="reg_email">Email:</label>
                            <input type="email" id="reg_email" name="reg_email" required>
                        </div>

                        <div class="form-group">
                            <label for="reg_password">Heslo:</label>
                            <input type="password" id="reg_password" name="reg_password" required>
                            <span class="small-text">Min. 6 znakov</span>
                        </div>

                        <div class="form-group">
                            <label for="reg_confirm">Potvrdenie hesla:</label>
                            <input type="password" id="reg_confirm" name="reg_confirm" required>
                        </div>

                        <button type="submit" name="user_signup" class="btn btn-primary">Zaregistrovať sa</button>
                    </form>

                    <div class="info-box">
                        <strong>✅ Výhody registrácie:</strong>
                        Sledovanie objednávok<br>
                        Rýchlejší checkout<br>
                        História nákupov
                    </div>
                </div>

                <!-- Admin login removed — page shows only user Login & Signup -->
            </div>

            <!-- INFORMAČNÁ SEKCIA -->
            <div style="margin-top: 40px; padding: 20px; background: #f0f0f0; border-radius: 8px; text-align: center;">
                <h4>❓ Potrebujete pomoc?</h4>
                <p>Ak ste stratili prihlasovacie údaje alebo máte otázky, <strong><a href="index.php">návštívte domovskú stránku</a></strong>.</p>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 <?php echo SITE_NAME; ?>. Všetky práva vyhradené.</p>
        </div>
    </footer>
</body>
</html>
