<?php
/**
 * Checkout - Simulácia platby kartou
 */

require_once 'inc/config.php';
require_once 'inc/db.php';

// Ak je košík prázdny, presmerovať na košík
if (empty($_SESSION['cart'])) {
    header('Location: ' . SITE_URL . '/cart.php');
    exit();
}

// Spracovanie platobného formulára
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    // Validácia údajov
    $cardholder_name = isset($_POST['cardholder_name']) ? trim($_POST['cardholder_name']) : '';
    $card_number = isset($_POST['card_number']) ? trim($_POST['card_number']) : '';
    $expiry_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : '';
    $cvv = isset($_POST['cvv']) ? trim($_POST['cvv']) : '';

    // Validácia mena
    if (empty($cardholder_name) || strlen($cardholder_name) < 3) {
        $errors['cardholder_name'] = 'Meno držiteľa je povinné (min. 3 znaky)';
    }

    // Validácia čísla karty (len číslice, 16 znakov)
    if (empty($card_number) || !preg_match('/^[0-9]{16}$/', str_replace(' ', '', $card_number))) {
        $errors['card_number'] = 'Číslo karty musí mať 16 číslic';
    }

    // Validácia dátumu (MM/YY alebo MM/YYYY)
    if (empty($expiry_date) || !preg_match('/^(0[1-9]|1[0-2])\/\d{2,4}$/', $expiry_date)) {
        $errors['expiry_date'] = 'Dátum musí byť vo formáte MM/YY alebo MM/YYYY';
    } else {
        // Kontrola, či nie je karta expirovaná
        list($month, $year) = explode('/', $expiry_date);
        $year = intval($year);
        if ($year < 100) {
            $year += 2000;
        }
        $current_year = intval(date('Y'));
        $current_month = intval(date('m'));

        if ($year < $current_year || ($year === $current_year && $month < $current_month)) {
            $errors['expiry_date'] = 'Karta je expirovaná';
        }
    }

    // Validácia CVV (3-4 číslice)
    if (empty($cvv) || !preg_match('/^[0-9]{3,4}$/', $cvv)) {
        $errors['cvv'] = 'CVV musí mať 3-4 číslice';
    }

    // Ak nie sú chyby, vytvoríme objednávku (simulácia platby)
    if (empty($errors)) {
        // Výpočet celkovej ceny
        $total_price = 0;
        $cart_data = [];
        
        foreach (array_keys($_SESSION['cart']) as $car_id) {
            $stmt = $mysqli->prepare("SELECT id, brand, model, price FROM cars WHERE id = ?");
            $stmt->bind_param("i", $car_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $car = $result->fetch_assoc();
                $cart_data[] = $car;
                $total_price += $car['price'];
            }
            $stmt->close();
        }

        // Vygenerovanie čísla objednávky
        $order_number = generateOrderNumber();

        // Vloženie objednávky do databázy
        $stmt = $mysqli->prepare("
            INSERT INTO orders (order_number, cardholder_name, total_price, status)
            VALUES (?, ?, ?, 'completed')
        ");
        $stmt->bind_param("ssd", $order_number, $cardholder_name, $total_price);
        
        if ($stmt->execute()) {
            $order_id = $mysqli->insert_id;
            $stmt->close();

            // Vloženie položiek objednávky
            foreach ($cart_data as $car) {
                $stmt = $mysqli->prepare("
                    INSERT INTO order_items (order_id, car_id, brand, model, price)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iissd", $order_id, $car['id'], $car['brand'], $car['model'], $car['price']);
                $stmt->execute();
                $stmt->close();
            }

            // Vyčistenie košíka
            $_SESSION['cart'] = [];
            $success = true;
            $_SESSION['order_number'] = $order_number;
            $_SESSION['order_cardholder'] = $cardholder_name;
            $_SESSION['order_total'] = $total_price;
        }
    }
}

// Načítanie áut v košíku pre zobrazenie
$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    foreach (array_keys($_SESSION['cart']) as $car_id) {
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
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platba - <?php echo SITE_NAME; ?></title>
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
                        <li><a href="cart.php">Košík <span class="cart-count">0</span></a></li>
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
            <?php if ($success): ?>
                <!-- POTVRDENENIE OBJEDNÁVKY -->
                <div class="checkout-success">
                    <div class="success-icon">✓</div>
                    <h2>Objednávka bola úspešne vytvorená!</h2>
                    
                    <div class="order-confirmation">
                        <div class="confirmation-item">
                            <span>Číslo objednávky:</span>
                            <strong><?php echo escape($_SESSION['order_number']); ?></strong>
                        </div>
                        <div class="confirmation-item">
                            <span>Meno:</span>
                            <strong><?php echo escape($_SESSION['order_cardholder']); ?></strong>
                        </div>
                        <div class="confirmation-item">
                            <span>Celková cena:</span>
                            <strong><?php echo formatPrice($_SESSION['order_total']); ?></strong>
                        </div>
                    </div>

                    <p class="confirmation-message">
                        Ďakujeme za vašu objednávku! Informácie o doručení vám budú poslané na vašu emailovú adresu.
                    </p>

                    <a href="index.php" class="btn btn-primary">Pokračovať v nákupe</a>
                </div>
            <?php else: ?>
                <!-- PLATOBNÝ FORMULÁR -->
                <a href="cart.php" class="btn btn-secondary" style="margin-bottom: 20px;">← Späť do košíka</a>

                <div class="checkout-container">
                    <!-- ZOZNAM ÁUT -->
                    <div class="checkout-items">
                        <h3>Objednané autá</h3>
                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item">
                                    <span><?php echo escape($item['brand'] . ' ' . $item['model']); ?> (<?php echo $item['year']; ?>)</span>
                                    <span class="price"><?php echo formatPrice($item['price']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="order-total">
                            <span>Spolu:</span>
                            <strong><?php echo formatPrice($total_price); ?></strong>
                        </div>
                    </div>

                    <!-- PLATOBNÝ FORMULÁR -->
                    <div class="checkout-form">
                        <h3>Platba kartou</h3>
                        <p class="form-info">* Toto je SIMULÁCIA platby. Žiadna reálna transakcia nebude vykonaná.</p>

                        <form method="POST" class="payment-form">
                            <!-- Meno držiteľa karty -->
                            <div class="form-group">
                                <label for="cardholder_name">Meno držiteľa karty *</label>
                                <input 
                                    type="text" 
                                    id="cardholder_name" 
                                    name="cardholder_name" 
                                    value="<?php echo escape($cardholder_name ?? ''); ?>"
                                    placeholder="Janko Mrkvička"
                                    required
                                >
                                <?php if (isset($errors['cardholder_name'])): ?>
                                    <span class="error"><?php echo escape($errors['cardholder_name']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Číslo karty -->
                            <div class="form-group">
                                <label for="card_number">Číslo karty *</label>
                                <input 
                                    type="text" 
                                    id="card_number" 
                                    name="card_number" 
                                    placeholder="1234 5678 9012 3456"
                                    maxlength="19"
                                    required
                                    pattern="[0-9\s]+"
                                >
                                <?php if (isset($errors['card_number'])): ?>
                                    <span class="error"><?php echo escape($errors['card_number']); ?></span>
                                <?php endif; ?>
                                <small>Skúšobné čísla: 4111111111111111 alebo 5555555555554444</small>
                            </div>

                            <!-- Dátum platnosti a CVV -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Dátum platnosti *</label>
                                    <input 
                                        type="text" 
                                        id="expiry_date" 
                                        name="expiry_date" 
                                        placeholder="MM/YY"
                                        maxlength="5"
                                        required
                                        pattern="\d{2}/\d{2,4}"
                                    >
                                    <?php if (isset($errors['expiry_date'])): ?>
                                        <span class="error"><?php echo escape($errors['expiry_date']); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="cvv">CVV *</label>
                                    <input 
                                        type="text" 
                                        id="cvv" 
                                        name="cvv" 
                                        placeholder="123"
                                        maxlength="4"
                                        required
                                        pattern="\d{3,4}"
                                    >
                                    <?php if (isset($errors['cvv'])): ?>
                                        <span class="error"><?php echo escape($errors['cvv']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Gombíky -->
                            <div class="form-actions">
                                <button type="submit" name="pay" class="btn btn-primary btn-large">
                                    Zaplatiť <?php echo formatPrice($total_price); ?>
                                </button>
                                <a href="cart.php" class="btn btn-secondary">Zrušiť</a>
                            </div>
                        </form>
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

    <!-- Skriptík na formátovanie čísla karty -->
    <script>
        const cardNumberInput = document.getElementById('card_number');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
            });
        }
    </script>
</body>
</html>
