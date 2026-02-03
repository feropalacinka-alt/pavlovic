<?php
/**
 * Admin sekcia - správa áut
 */

require_once 'inc/config.php';
require_once 'inc/db.php';

// Presmerovanie na admin login ak nie je admin prihlásený
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ' . SITE_URL . '/admin-login.php');
    exit();
}

// Odhlásenie
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . SITE_URL . '/index.php');
    exit();
}

// Spracovanie akcií (len pre prihláseného admina)
if (isset($_SESSION['admin_logged_in'])) {
    // Pridanie nového auta
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_car'])) {
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $engine_type = trim($_POST['engine_type'] ?? '');
        $fuel_type = trim($_POST['fuel_type'] ?? '');
        $transmission = trim($_POST['transmission'] ?? '');
        $power = intval($_POST['power'] ?? 0);
        $mileage = intval($_POST['mileage'] ?? 0);
        $color = trim($_POST['color'] ?? '');

        // Validácia
        if (!empty($brand) && !empty($model) && $year > 1900 && $price > 0) {
            $stmt = $mysqli->prepare("
                INSERT INTO cars (brand, model, year, price, description, engine_type, fuel_type, transmission, power, mileage, color)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $types = "ssidsssiii";
            $stmt->bind_param($types, $brand, $model, $year, $price, $description, $engine_type, $fuel_type, $transmission, $power, $mileage, $color);

            if ($stmt->execute()) {
                $car_id = $mysqli->insert_id;

                // Spracovanie nahrávania obrázkov
                if (!empty($_FILES['images']['name'][0])) {
                    // Vytvorenie priečinka, ak neexistuje
                    if (!is_dir(UPLOAD_PATH)) {
                        mkdir(UPLOAD_PATH, 0755, true);
                    }

                    $is_main = true;
                    foreach ($_FILES['images']['name'] as $key => $filename) {
                        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                            $tmp_file = $_FILES['images']['tmp_name'][$key];
                            $file_size = $_FILES['images']['size'][$key];
                            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                            // Validácia
                            if ($file_size <= MAX_FILE_SIZE && in_array($file_ext, ALLOWED_EXTENSIONS)) {
                                $new_filename = 'car_' . $car_id . '_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                                $upload_file = UPLOAD_PATH . $new_filename;

                                if (move_uploaded_file($tmp_file, $upload_file)) {
                                    $image_url = UPLOAD_DIR . $new_filename;

                                    // Uloženie údajov o obrázku do databázy
                                    $stmt2 = $mysqli->prepare("
                                        INSERT INTO car_images (car_id, image_url, is_main)
                                        VALUES (?, ?, ?)
                                    ");
                                    $stmt2->bind_param("isi", $car_id, $image_url, $is_main);
                                    $stmt2->execute();
                                    $stmt2->close();

                                    $is_main = false;
                                }
                            }
                        }
                    }
                }

                setFlashMessage('Auto bolo úspešne pridané!', 'success');
            }
            $stmt->close();
        } else {
            setFlashMessage('Skontrolujte vyplnené údaje', 'error');
        }

        header('Location: ' . SITE_URL . '/admin.php');
        exit();
    }

    // Úprava auta
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_car'])) {
        $car_id = intval($_POST['car_id']);
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $engine_type = trim($_POST['engine_type'] ?? '');
        $fuel_type = trim($_POST['fuel_type'] ?? '');
        $transmission = trim($_POST['transmission'] ?? '');
        $power = intval($_POST['power'] ?? 0);
        $mileage = intval($_POST['mileage'] ?? 0);
        $color = trim($_POST['color'] ?? '');

        if (!empty($brand) && !empty($model) && $year > 1900 && $price > 0) {
            $stmt = $mysqli->prepare("
                UPDATE cars 
                SET brand=?, model=?, year=?, price=?, description=?, engine_type=?, fuel_type=?, transmission=?, power=?, mileage=?, color=?
                WHERE id=?
            ");

            $types = "ssidsssiiiii";
            $stmt->bind_param($types, $brand, $model, $year, $price, $description, $engine_type, $fuel_type, $transmission, $power, $mileage, $color, $car_id);

            if ($stmt->execute()) {
                // Spracovanie nových obrázkov
                if (!empty($_FILES['images']['name'][0])) {
                    if (!is_dir(UPLOAD_PATH)) {
                        mkdir(UPLOAD_PATH, 0755, true);
                    }

                    foreach ($_FILES['images']['name'] as $key => $filename) {
                        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                            $tmp_file = $_FILES['images']['tmp_name'][$key];
                            $file_size = $_FILES['images']['size'][$key];
                            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                            if ($file_size <= MAX_FILE_SIZE && in_array($file_ext, ALLOWED_EXTENSIONS)) {
                                $new_filename = 'car_' . $car_id . '_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                                $upload_file = UPLOAD_PATH . $new_filename;

                                if (move_uploaded_file($tmp_file, $upload_file)) {
                                    $image_url = UPLOAD_DIR . $new_filename;

                                    $stmt2 = $mysqli->prepare("
                                        INSERT INTO car_images (car_id, image_url, is_main)
                                        VALUES (?, ?, 0)
                                    ");
                                    $stmt2->bind_param("is", $car_id, $image_url);
                                    $stmt2->execute();
                                    $stmt2->close();
                                }
                            }
                        }
                    }
                }

                setFlashMessage('Auto bolo úspešne upravené!', 'success');
            }
            $stmt->close();
        } else {
            setFlashMessage('Skontrolujte vyplnené údaje', 'error');
        }

        header('Location: ' . SITE_URL . '/admin.php');
        exit();
    }

    // Mazanie auta
    if (isset($_GET['delete_car'])) {
        $car_id = intval($_GET['delete_car']);

        // Načítanie obrázkov na vymazanie
        $images_result = $mysqli->query("SELECT image_url FROM car_images WHERE car_id = $car_id");
        if ($images_result) {
            while ($img = $images_result->fetch_assoc()) {
                if (file_exists($img['image_url'])) {
                    unlink($img['image_url']);
                }
            }
        }

        // Vymazanie auta z databázy
        $stmt = $mysqli->prepare("DELETE FROM cars WHERE id = ?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $stmt->close();

        setFlashMessage('Auto bolo odstránené!', 'success');
        header('Location: ' . SITE_URL . '/admin.php');
        exit();
    }

    // Mazanie obrázku
    if (isset($_GET['delete_image'])) {
        $image_id = intval($_GET['delete_image']);

        $image_result = $mysqli->query("SELECT image_url FROM car_images WHERE id = $image_id");
        if ($image_result && $image = $image_result->fetch_assoc()) {
            if (file_exists($image['image_url'])) {
                unlink($image['image_url']);
            }

            $stmt = $mysqli->prepare("DELETE FROM car_images WHERE id = ?");
            $stmt->bind_param("i", $image_id);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: ' . SITE_URL . '/admin.php');
        exit();
    }
}

// Načítanie všetkých áut pre admin panel
$cars = [];
if (isset($_SESSION['admin_logged_in'])) {
    $result = $mysqli->query("SELECT * FROM cars ORDER BY created_at DESC");
    if ($result) {
        while ($car = $result->fetch_assoc()) {
            $car['images'] = [];
            $images_result = $mysqli->query("SELECT * FROM car_images WHERE car_id = " . $car['id']);
            if ($images_result) {
                while ($img = $images_result->fetch_assoc()) {
                    $car['images'][] = $img;
                }
            }
            $cars[] = $car;
        }
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-nav { margin-bottom: 30px; }
        .admin-nav button { margin-right: 10px; }
        .modal { display: none; }
        .modal.active { display: block; }
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
                        <li><a href="cart.php">Košík <span class="cart-count">0</span></a></li>
                        <li><a href="admin.php" class="active">Admin</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo escape($flash['type']); ?>">
                    <?php echo escape($flash['message']); ?>
                </div>
            <?php endif; ?>

            <!-- ADMIN PANEL -->
            <div class="admin-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Admin panel</h2>
                    <a href="admin.php?logout=1" class="btn btn-danger">Odhlásiť sa</a>
                </div>

                <!-- TLAČIDLÁ AKCIÍ -->
                <div class="admin-nav">
                    <button onclick="toggleModal('addCarModal')" class="btn btn-primary">+ Pridať nové auto</button>
                </div>

                <!-- ZOZNAM ÁUT -->
                <h3>Dostupné autá</h3>
                    <div class="admin-cars-list">
                        <?php if (empty($cars)): ?>
                            <p>Žiadne autá nie sú dostupné.</p>
                        <?php else: ?>
                            <?php foreach ($cars as $car): ?>
                                <div class="admin-car-item">
                                    <div class="admin-car-info">
                                        <h4><?php echo escape($car['brand'] . ' ' . $car['model']); ?></h4>
                                        <p>
                                            <span class="badge"><?php echo $car['year']; ?></span>
                                            <span class="badge"><?php echo formatPrice($car['price']); ?></span>
                                            <span class="badge"><?php echo number_format($car['mileage']); ?> km</span>
                                        </p>

                                        <!-- Obrázky -->
                                        <?php if (!empty($car['images'])): ?>
                                            <div class="admin-images">
                                                <?php foreach ($car['images'] as $img): ?>
                                                    <div class="admin-image-item">
                                                        <img src="<?php echo escape($img['image_url']); ?>" alt="Obrázok">
                                                        <a href="admin.php?delete_image=<?php echo $img['id']; ?>" onclick="return confirm('Zmazať obrázok?')" class="delete-link">✕</a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="admin-car-actions">
                                        <button onclick="editCar(<?php echo htmlspecialchars(json_encode($car)); ?>)" class="btn btn-secondary btn-small">Upraviť</button>
                                        <a href="admin.php?delete_car=<?php echo $car['id']; ?>" onclick="return confirm('Zmazať auto?')" class="btn btn-danger btn-small">Zmazať</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- MODÁLNE OKNO - PRIDANIE AUTA -->
                <div id="addCarModal" class="modal-overlay modal">
                    <div class="modal-content">
                        <span class="close" onclick="toggleModal('addCarModal')">&times;</span>
                        <h3>Pridať nové auto</h3>

                        <form method="POST" enctype="multipart/form-data" class="car-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="brand">Značka *</label>
                                    <input type="text" id="brand" name="brand" required>
                                </div>
                                <div class="form-group">
                                    <label for="model">Model *</label>
                                    <input type="text" id="model" name="model" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="year">Rok výroby *</label>
                                    <input type="number" id="year" name="year" min="1900" max="2099" required>
                                </div>
                                <div class="form-group">
                                    <label for="price">Cena (€) *</label>
                                    <input type="number" id="price" name="price" min="0" step="0.01" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Popis</label>
                                <textarea id="description" name="description" rows="4"></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="engine_type">Typ motora</label>
                                    <input type="text" id="engine_type" name="engine_type">
                                </div>
                                <div class="form-group">
                                    <label for="fuel_type">Palivo</label>
                                    <input type="text" id="fuel_type" name="fuel_type">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="transmission">Prevodovka</label>
                                    <input type="text" id="transmission" name="transmission">
                                </div>
                                <div class="form-group">
                                    <label for="power">Výkon (kW)</label>
                                    <input type="number" id="power" name="power" min="0">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="mileage">Najazdené km</label>
                                    <input type="number" id="mileage" name="mileage" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="color">Farba</label>
                                    <input type="text" id="color" name="color">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="images">Obrázky (JPG, PNG, GIF - max 5MB)</label>
                                <input type="file" id="images" name="images[]" multiple accept="image/*">
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="add_car" class="btn btn-primary">Pridať auto</button>
                                <button type="button" onclick="toggleModal('addCarModal')" class="btn btn-secondary">Zrušiť</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- MODÁLNE OKNO - ÚPRAVA AUTA -->
                <div id="editCarModal" class="modal-overlay modal">
                    <div class="modal-content">
                        <span class="close" onclick="toggleModal('editCarModal')">&times;</span>
                        <h3>Upraviť auto</h3>

                        <form method="POST" enctype="multipart/form-data" class="car-form">
                            <input type="hidden" id="edit_car_id" name="car_id">

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_brand">Značka *</label>
                                    <input type="text" id="edit_brand" name="brand" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_model">Model *</label>
                                    <input type="text" id="edit_model" name="model" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_year">Rok výroby *</label>
                                    <input type="number" id="edit_year" name="year" min="1900" max="2099" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_price">Cena (€) *</label>
                                    <input type="number" id="edit_price" name="price" min="0" step="0.01" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="edit_description">Popis</label>
                                <textarea id="edit_description" name="description" rows="4"></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_engine_type">Typ motora</label>
                                    <input type="text" id="edit_engine_type" name="engine_type">
                                </div>
                                <div class="form-group">
                                    <label for="edit_fuel_type">Palivo</label>
                                    <input type="text" id="edit_fuel_type" name="fuel_type">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_transmission">Prevodovka</label>
                                    <input type="text" id="edit_transmission" name="transmission">
                                </div>
                                <div class="form-group">
                                    <label for="edit_power">Výkon (kW)</label>
                                    <input type="number" id="edit_power" name="power" min="0">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_mileage">Najazdené km</label>
                                    <input type="number" id="edit_mileage" name="mileage" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="edit_color">Farba</label>
                                    <input type="text" id="edit_color" name="color">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="edit_images">Pridať obrázky (JPG, PNG, GIF - max 5MB)</label>
                                <input type="file" id="edit_images" name="images[]" multiple accept="image/*">
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="edit_car" class="btn btn-primary">Uložiť zmeny</button>
                                <button type="button" onclick="toggleModal('editCarModal')" class="btn btn-secondary">Zrušiť</button>
                            </div>
                        </form>
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

    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('active');
        }

        function editCar(car) {
            document.getElementById('edit_car_id').value = car.id;
            document.getElementById('edit_brand').value = car.brand;
            document.getElementById('edit_model').value = car.model;
            document.getElementById('edit_year').value = car.year;
            document.getElementById('edit_price').value = car.price;
            document.getElementById('edit_description').value = car.description;
            document.getElementById('edit_engine_type').value = car.engine_type;
            document.getElementById('edit_fuel_type').value = car.fuel_type;
            document.getElementById('edit_transmission').value = car.transmission;
            document.getElementById('edit_power').value = car.power;
            document.getElementById('edit_mileage').value = car.mileage;
            document.getElementById('edit_color').value = car.color;

            toggleModal('editCarModal');
        }

        // Zatváranie modálu kliknutím mimo
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>
