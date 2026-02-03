<?php
/**
 * Úvodná stránka - zoznam áut
 */

require_once 'inc/config.php';
require_once 'inc/db.php';

// Mapovanie áut na obrázky z imgs priečinka podľa názvu
$image_mapping = [
    'golf' => 'imgs/golf.jpg',
    'bmw' => 'imgs/bmw.jpg',
    'mercedes' => 'imgs/mercedes.jpg',
    'audi' => 'imgs/audi.jpg',
    'corolla' => 'imgs/toyota.jpg',
    'toyota' => 'imgs/toyota.jpg'
];

// Načítanie všetkých áut z databázy
$result = $mysqli->query("
    SELECT c.*, ci.image_url as first_image 
    FROM cars c 
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_main = 1
    ORDER BY c.created_at DESC
");

$cars = [];
if ($result) {
    while ($car = $result->fetch_assoc()) {
        // Dynamicky nájdi obrázok podľa modelu alebo značky
        $model_lower = strtolower($car['model'] ?? '');
        $brand_lower = strtolower($car['brand'] ?? '');
        
        // Priorita: database image → model mapping → brand mapping
        if ($car['first_image'] && !empty($car['first_image'])) {
            // Databáza má obrázok - použij ho
            $car['display_image'] = $car['first_image'];
        } elseif ($model_lower && isset($image_mapping[$model_lower])) {
            // Nájdi podľa modelu
            $car['display_image'] = $image_mapping[$model_lower];
        } elseif ($brand_lower && isset($image_mapping[$brand_lower])) {
            // Fallback: nájdi podľa značky
            $car['display_image'] = $image_mapping[$brand_lower];
        } else {
            // Neexistuje - null
            $car['display_image'] = null;
        }
        
        $cars[] = $car;
    }
}

// Spracovanie pridania do košíka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $car_id = intval($_POST['car_id']);
    
    // Overenie, že auto existuje
    $stmt = $mysqli->prepare("SELECT id FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $check_result = $stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        if (!isset($_SESSION['cart'][$car_id])) {
            $_SESSION['cart'][$car_id] = 1;
            setFlashMessage('Auto bolo pridané do košíka!', 'success');
        } else {
            setFlashMessage('Toto auto je už v košíku.', 'info');
        }
    }
    $stmt->close();
    
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Predaj automobilov</title>
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
                        <li><a href="index.php" class="active">Domov</a></li>
                        <li><a href="cart.php">
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

            <!-- HERO SEKCIA -->
            <section class="hero">
                <h2>Vítajte v <?php echo SITE_NAME; ?></h2>
                <p>Nájdite si svoj vysnívaný automobil</p>
            </section>

            <!-- ZOZNAM ÁUT -->
            <section class="cars-section">
                <h3>Dostupné autá</h3>
                
                <?php if (empty($cars)): ?>
                    <div class="empty-state">
                        <p>Žiadne autá nie sú momentálne dostupné.</p>
                    </div>
                <?php else: ?>
                    <div class="cars-grid">
                        <?php foreach ($cars as $car): ?>
                            <div class="car-card">
                                <!-- Obrázok auta -->
                                <div class="car-image">
                                    <?php if (!empty($car['display_image'])): ?>
                                        <img src="<?php echo escape($car['display_image']); ?>" 
                                             alt="<?php echo escape($car['brand'] . ' ' . $car['model']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">Bez obrázku</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Údaje o aute -->
                                <div class="car-info">
                                    <h4><?php echo escape($car['brand'] . ' ' . $car['model']); ?></h4>
                                    
                                    <div class="car-details">
                                        <span class="year"><?php echo $car['year']; ?></span>
                                        <span class="mileage"><?php echo number_format($car['mileage']); ?> km</span>
                                        <span class="color"><?php echo escape($car['color']); ?></span>
                                    </div>

                                    <p class="description"><?php echo escape(substr($car['description'], 0, 80) . '...'); ?></p>

                                    <div class="car-footer">
                                        <span class="price"><?php echo formatPrice($car['price']); ?></span>
                                        <div class="actions">
                                            <a href="detail.php?id=<?php echo $car['id']; ?>" class="btn btn-secondary">Detail</a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                                <button type="submit" name="add_to_cart" class="btn btn-primary">Do košíka</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
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
