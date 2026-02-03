<?php
/**
 * Nákupný košík
 */

require_once 'inc/config.php';
require_once 'inc/db.php';

// Spracovanie odstránenia auta z košíka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $car_id = intval($_POST['car_id']);
    
    if (isset($_SESSION['cart'][$car_id])) {
        unset($_SESSION['cart'][$car_id]);
        setFlashMessage('Auto bolo odstránené z košíka.', 'info');
    }
    
    header('Location: ' . SITE_URL . '/cart.php');
    exit();
}

// Načítanie áut v košíku
$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    foreach (array_keys($_SESSION['cart']) as $car_id) {
        // Bezpečný dotaz s prepared statement
        $stmt = $mysqli->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $car = $result->fetch_assoc();
            $cart_items[] = $car;
            $total_price += $car['price'];
        }
        $stmt->close();
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Košík - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
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
                        <li><a href="cart.php" class="active">
                            Košík 
                            <span class="cart-count">
                                <?php echo count($_SESSION['cart']); ?>
                            </span>
                        </a></li>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null): ?>
                            <li><a href="profile.php">👤 <?php echo escape($_SESSION['user_name'] ?? 'Profil'); ?></a></li>
                            <li><a href="logout.php">Odhlásenie</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login/Signup</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">
            <!-- Flash správa -->
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo escape($flash['type']); ?>">
                    <?php echo escape($flash['message']); ?>
                </div>
            <?php endif; ?>

            <a href="index.php" class="btn btn-secondary" style="margin-bottom: 20px;">← Späť na zoznam</a>

            <h2>Nákupný košík</h2>

            <?php if (empty($cart_items)): ?>
                <!-- PRÁZDNY KOŠÍK -->
                <div class="empty-cart">
                    <p>Váš košík je prázdny.</p>
                    <a href="index.php" class="btn btn-primary">Pokračovať v nákupe</a>
                </div>
            <?php else: ?>
                <!-- POLOŽKY V KOŠÍKU -->
                <div class="cart-container">
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Auto</th>
                                    <th>Rok</th>
                                    <th>Cena</th>
                                    <th>Akcia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <a href="detail.php?id=<?php echo $item['id']; ?>">
                                                <?php echo escape($item['brand'] . ' ' . $item['model']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo $item['year']; ?></td>
                                        <td class="price"><?php echo formatPrice($item['price']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="car_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" name="remove_from_cart" class="btn btn-small btn-danger">
                                                    Odstrániť
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- SÚHRN OBJEDNÁVKY -->
                    <div class="cart-summary">
                        <h3>Súhrn objednávky</h3>
                        <div class="summary-item">
                            <span>Počet áut:</span>
                            <strong><?php echo count($cart_items); ?></strong>
                        </div>
                        <div class="summary-item total">
                            <span>Celková cena:</span>
                            <strong><?php echo formatPrice($total_price); ?></strong>
                        </div>
                        <a href="checkout.php" class="btn btn-primary btn-large">
                            Pokračovať k platbe
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            Pokračovať v nákupe
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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
