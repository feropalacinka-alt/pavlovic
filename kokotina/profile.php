<?php
/**
 * Profil používateľa
 */

require_once 'inc/config.php';
require_once 'inc/db.php';

// Presmerovanie ak nie je prihlásený
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

// Načítanie informácií o používateľovi
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT id, email, first_name, last_name, phone, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Načítanie objednávok používateľa
$orders = [];
$order_result = $mysqli->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
if ($order_result) {
    while ($order = $order_result->fetch_assoc()) {
        $orders[] = $order;
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 900px;
            margin: 30px auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .profile-card {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .profile-card h3 {
            margin-top: 0;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-row {
            display: flex;
            flex-direction: column;
        }

        .info-row label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .info-row span {
            color: #666;
        }

        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .orders-section {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .orders-section h3 {
            margin-top: 0;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .order-item {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .order-item h4 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .order-detail {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        @media (max-width: 768px) {
            .profile-container {
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
                        <li><a href="profile.php" class="active">👤 Profil</a></li>
                        <li><a href="logout.php">Odhlásenie</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">
            <a href="index.php" class="btn btn-secondary" style="margin-bottom: 20px; display: inline-block; max-width: 150px;">← Späť na domov</a>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo escape($flash['type']); ?>" style="margin-bottom: 20px;">
                    <?php echo escape($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="profile-container">
                <!-- PROFIL KARTY -->
                <div class="profile-card">
                    <h3>👤 Moj Profil</h3>

                    <div class="profile-info">
                        <div class="info-row">
                            <label>Email:</label>
                            <span><?php echo escape($user['email']); ?></span>
                        </div>

                        <div class="info-row">
                            <label>Meno:</label>
                            <span><?php echo escape($user['first_name'] ?? '-'); ?></span>
                        </div>

                        <div class="info-row">
                            <label>Priezvisko:</label>
                            <span><?php echo escape($user['last_name'] ?? '-'); ?></span>
                        </div>

                        <div class="info-row">
                            <label>Telefón:</label>
                            <span><?php echo escape($user['phone'] ?? '-'); ?></span>
                        </div>

                        <div class="info-row">
                            <label>Registrovaný od:</label>
                            <span><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <a href="edit-profile.php" class="btn btn-primary">Upraviť profil</a>
                        <a href="logout.php" class="btn btn-danger">Odhlásiť sa</a>
                    </div>
                </div>

                <!-- OBJEDNÁVKY -->
                <div class="orders-section">
                    <h3>📋 Moje Objednávky</h3>

                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <p>Nemáte žiadne objednávky.</p>
                            <a href="index.php" class="btn btn-primary" style="max-width: 200px; margin-top: 20px;">Začať nákupovať</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item">
                                <h4>Objednávka #<?php echo escape($order['order_number']); ?></h4>
                                <div class="order-detail">
                                    <strong>Číslo:</strong> <?php echo escape($order['order_number']); ?>
                                </div>
                                <div class="order-detail">
                                    <strong>Suma:</strong> <?php echo formatPrice($order['total_price']); ?>
                                </div>
                                <div class="order-detail">
                                    <strong>Stav:</strong> 
                                    <span style="color: #28a745; font-weight: 600;">
                                        <?php echo ucfirst(escape($order['status'])); ?>
                                    </span>
                                </div>
                                <div class="order-detail">
                                    <strong>Dátum:</strong> <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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
