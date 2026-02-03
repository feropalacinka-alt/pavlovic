<?php
/**
 * Detail auta
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

// Kontrola ID auta
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}

$car_id = intval($_GET['id']);

// Načítanie údajov o aute
$stmt = $mysqli->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}

$car = $result->fetch_assoc();
$stmt->close();

// Načítanie všetkých fotiek auta
$images_result = $mysqli->query("SELECT * FROM car_images WHERE car_id = $car_id ORDER BY is_main DESC");
$images = [];
if ($images_result) {
    while ($img = $images_result->fetch_assoc()) {
        $images[] = $img;
    }
}

// Pokúsiť sa nájsť obrázok podľa modelu, prípadne podľa značky (fallback)
$model_lower = strtolower($car['model'] ?? '');
$brand_lower = strtolower($car['brand'] ?? '');

if (empty($images)) {
    // Databáza nemá obrázky - použij mapping
    if ($model_lower && isset($image_mapping[$model_lower])) {
        $images[] = ['image_url' => $image_mapping[$model_lower], 'is_main' => 1];
    } elseif ($brand_lower && isset($image_mapping[$brand_lower])) {
        $images[] = ['image_url' => $image_mapping[$brand_lower], 'is_main' => 1];
    }
} else {
    // Databáza má obrázky - pridaj mapping na začiatok ako primárny
    if ($model_lower && isset($image_mapping[$model_lower])) {
        array_unshift($images, ['image_url' => $image_mapping[$model_lower], 'is_main' => 1]);
    } elseif ($brand_lower && isset($image_mapping[$brand_lower])) {
        array_unshift($images, ['image_url' => $image_mapping[$brand_lower], 'is_main' => 1]);
    }
}

// Spracovanie pridania do košíka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'][$car_id])) {
        $_SESSION['cart'][$car_id] = 1;
        $message = 'Auto bolo pridané do košíka!';
    } else {
        $message = 'Toto auto je už v košíku.';
    }
    
    header('Location: ' . SITE_URL . '/detail.php?id=' . $car_id . '&added=1');
    exit();
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($car['brand'] . ' ' . $car['model']); ?> - <?php echo SITE_NAME; ?></title>
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
            <?php if (isset($_GET['added'])): ?>
                <div class="alert alert-success">
                    Auto bolo pridané do košíka!
                </div>
            <?php endif; ?>

            <a href="index.php" class="btn btn-secondary" style="margin-bottom: 20px;">← Späť na zoznam</a>

            <div class="detail-container">
                <!-- ĽAVÁ STRANA - FOTKY -->
                <div class="detail-images">
                    <!-- Hlavný obrázok -->
                    <div class="main-image">
                        <?php 
                        $mainImage = !empty($images) ? $images[0]['image_url'] : $car['image_url'];
                        if (!empty($mainImage)):
                        ?>
                            <img id="mainImg" src="<?php echo escape($mainImage); ?>" 
                                 alt="<?php echo escape($car['brand'] . ' ' . $car['model']); ?>">
                        <?php else: ?>
                            <div class="no-image">Bez obrázku</div>
                        <?php endif; ?>
                    </div>

                    <!-- Miniatúry obrázkov -->
                    <?php if (!empty($images)): ?>
                        <div class="thumbnails">
                            <?php foreach ($images as $image): ?>
                                <img src="<?php echo escape($image['image_url']); ?>" 
                                     alt="Thumbnail"
                                     class="thumbnail"
                                     onclick="changeImage('<?php echo escape($image['image_url']); ?>')">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PRAVÁ STRANA - ÚDAJE -->
                <div class="detail-info">
                    <!-- ZÁKLADNÉ ÚDAJE -->
                    <h2><?php echo escape($car['brand'] . ' ' . $car['model']); ?></h2>
                    
                    <div class="detail-specs">
                        <div class="spec">
                            <strong>Rok výroby:</strong>
                            <span><?php echo $car['year']; ?></span>
                        </div>
                        <div class="spec">
                            <strong>Cena:</strong>
                            <span class="price"><?php echo formatPrice($car['price']); ?></span>
                        </div>
                    </div>

                    <!-- TECHNICKÉ PARAMETRE -->
                    <h3>Technické parametre</h3>
                    <div class="technical-specs">
                        <div class="spec-row">
                            <span class="spec-label">Typ motora:</span>
                            <span class="spec-value"><?php echo escape($car['engine_type']); ?></span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Palivo:</span>
                            <span class="spec-value"><?php echo escape($car['fuel_type']); ?></span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Prevodovka:</span>
                            <span class="spec-value"><?php echo escape($car['transmission']); ?></span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Výkon:</span>
                            <span class="spec-value"><?php echo $car['power']; ?> kW</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Najazdené km:</span>
                            <span class="spec-value"><?php echo number_format($car['mileage']); ?> km</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Farba:</span>
                            <span class="spec-value"><?php echo escape($car['color']); ?></span>
                        </div>
                    </div>

                    <!-- POPIS -->
                    <h3>Popis</h3>
                    <p class="description-text">
                        <?php echo escape($car['description']); ?>
                    </p>

                    <!-- TLAČIDLÁ -->
                    <div class="detail-actions">
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-large">
                                Pridať do košíka
                            </button>
                        </form>
                    </div>
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

    <!-- JAVASCRIPT -->
    <script>
        function changeImage(imageSrc) {
            document.getElementById('mainImg').src = imageSrc;
        }
    </script>
</body>
</html>
