🔐 SQL & PHP BEZPEČNOSŤ - PODROBNÝ VÝKLAD
==========================================

## ⚠️ TOP 5 BEZPEČNOSTNÝCH HROZIEB

### 1. SQL INJECTION (Najväčšia hrozba)

#### Ako to funguje - ÚTOK ❌

```php
// Aplikácia:
$id = $_GET['id']; // user Input
$query = "SELECT * FROM cars WHERE id = " . $id;
$result = $mysqli->query($query);

// Útočník zadá: ?id = 1 OR 1=1 --
// Skutočný SQL:
// SELECT * FROM cars WHERE id = 1 OR 1=1 --
// Výsledok: Vrátí VŠETKY autá (vrátane tých, ktoré by nemali byť viditeľné)

// Horšie: ?id = 1; DROP TABLE cars; --
// DELETE všetkých áut!
```

#### OCHRANA - Prepared Statements ✓

```php
// Správne:
$stmt = $mysqli->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Ako funguje:
// 1. SQL skript sa NAJPRV posunie na server (bez údajov)
// 2. Server sa pripraví na príjem podatkov
// 3. Údaje sa pošlú ODDELENE (ako parametre, nie ako textový vstup)
// 4. Server VŽDY vie, čo je SQL a čo sú údaje
// 5. Útok je NEMOŽNÝ

// Aj keď útočník zadá: 1; DROP TABLE cars; --
// Server to vidí ako STRING, nie ako SQL príkaz
```

#### Typy parametrov v bind_param:
```php
"i" - Integer (čísla)
"d" - Double (desatinné čísla)
"s" - String (text)
"b" - Blob (binárne dáta)

// Príklad:
$stmt->bind_param("isdss", $id, $price, $name, $description);
```

───────────────────────────────────────────────────

### 2. CROSS-SITE SCRIPTING (XSS) - Útok na JavaScript

#### Ako to funguje - ÚTOK ❌

```php
// Aplikácia:
<h1><?php echo $_GET['title']; ?></h1>

// Útočník zadá: ?title = <script>alert('hacked')</script>
// HTML sa stane:
// <h1><script>alert('hacked')</script></h1>
// Skript sa spustí!

// Horšie - Cookie/Session krádež:
// ?title = <script>
//   fetch('/steal.php?cookie=' + document.cookie)
// </script>
```

#### OCHRANA - Escape Output ✓

```php
// Správne:
<h1><?php echo escape($_GET['title']); ?></h1>

// Funkcia:
function escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Ako funguje:
// <  sa zmení na &lt;
// >  sa zmení na &gt;
// "  sa zmení na &quot;
// '  sa zmení na &#039;

// Útočník zadá: <script>alert('hack')</script>
// Zobrazí sa ako TEXT: &lt;script&gt;alert('hack')&lt;/script&gt;
// Skript sa NESPUSTÍ
```

#### Kde vždy escapovať:
```php
<!-- HTML atribúty -->
<img src="<?php echo escape($url); ?>">

<!-- HTML text -->
<p><?php echo escape($text); ?></p>

<!-- JavaScript -->
<script>
var title = "<?php echo escape($title); ?>";
</script>

<!-- URL query string -->
<a href="detail.php?id=<?php echo escape($id); ?>">

<!-- JSON -->
echo json_encode(['name' => escape($name)]);
```

───────────────────────────────────────────────────

### 3. BRUTE FORCE ÚTOK NA HESLO

#### Ako to funguje - ÚTOK ❌

```php
// Útočník má program, ktorý skúša všetky hesla:
// admin123, admin124, admin125... až milión pokusov
// Ak je heslo slabé alebo bez delay, priemerný čas:
// 5 číslic = 1 minúta
// 8 znakov = 22 minut
```

#### OCHRANA - Bcrypt Hashing ✓

```php
// Hashovanie pri registrácii:
$password = "admin123";
$hash = password_hash($password, PASSWORD_BCRYPT);
// $2y$10$... (65 znakov)

// Verifikácia pri logine:
if (password_verify($user_password, $stored_hash)) {
    // Správne heslo
}

// Prečo je bcrypt bezpečný:
// 1. POMALÝ - trvá 100ms (útočník: 1 000 000ms = 28 hodín na 1M pokusov)
// 2. SALT - náhodný reťazec zabráňuje rainbow tabuľkám
// 3. ITERÁCIE - 2^10 = 1024 kôl hašovania
// 4. ONE-WAY - nemožno z hashu získať originálne heslo
```

#### Cvičenie - Správne hesla:
```php
// ✓ DOBRÉ HESLO
"MyS3cur3P@ssw0rd!" // 16 znakov, zmiešané, špeciálne

// ❌ SLABÉ HESLO
"123456"           // Príliš jednoduché
"password"         // Celý text
"admin123"         // Predvídateľné
"abc12345"         // Sekvenčné
```

───────────────────────────────────────────────────

### 4. SESSION HIJACKING - Krádež session ID

#### Ako to funguje - ÚTOK ❌

```php
// Útočník získa session ID (z cookies):
// PHPSESSID=a1b2c3d4e5f6g7h8i9j0

// Potom sa vydáva za tohto používateľa:
// Posle ten istý PHPSESSID v request
// Server myslí, že je to ten istý používateľ
```

#### OCHRANA - Bezpečné Sessions ✓

```php
// V php.ini:
session.cookie_secure = On    // Len HTTPS
session.cookie_httponly = On  // Nie je dostupný JavaScript
session.cookie_samesite = "Strict" // Žiadne CSRF

// V kóde:
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php?login=1');
    exit();
}

// Regenerácia session ID po logine:
session_regenerate_id(true);
$_SESSION['admin_logged_in'] = true;

// Logout:
session_destroy();
```

───────────────────────────────────────────────────

### 5. FILE UPLOAD VULNERABILITIES

#### Ako to funguje - ÚTOK ❌

```php
// Útočník nahrá PHP skript ako "obrázok":
// "shell.jpg" v skutočnosti obsahuje PHP kód
// Web server to spustí
// Útočník má prístup k serveru

// Prípadne: overwrite existujúceho súboru
// Útokom na iných používateľov cez nahraté obrázky
```

#### OCHRANA - Správne File Upload ✓

```php
// 1. Kontrola veľkosti
if ($_FILES['image']['size'] > 5242880) { // 5MB
    die("Súbor je príliš veľký");
}

// 2. Kontrola typu súboru
$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
    die("Nepodporovaný typ súboru");
}

// 3. Bezpečné meno súboru (bez speciálnych znakov)
$new_filename = 'car_' . $car_id . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

// 4. Uloženie MIMO web root (ideálne)
// V tomto projekte: /uploads/ s .htaccess

// 5. Premenuj - nespúšťaj ako PHP
move_uploaded_file($tmp_file, UPLOAD_PATH . $new_filename);

// 6. .htaccess na zákaz spustenia PHP v uploads:
// <FilesMatch "\.php$">
//     Deny from all
// </FilesMatch>
```

═════════════════════════════════════════════════════

## 🛡️ BEST PRACTICES V TOMTO PROJEKTE

### ✓ V inc/db.php

```php
// Prepared statements na VŠETKY SQL dotazy
function executeSafeQuery($query, $params = [], $types = "") {
    global $mysqli;
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

// Nikdy nekonkatenácia SQL s user inputom!
// ❌ NIKDY: "WHERE id = " . $_GET['id']
// ✓ VŽDY: WHERE id = ? s bind_param
```

### ✓ V inc/config.php

```php
// Escape všetkých výstupov
function escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Bezpečné session
session_start();

// Definované konštanty (nie magic numbers)
define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
```

### ✓ V jednotlivých stránkach

```php
// Vždy overenie inputu:
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// Vždy escape output:
<h1><?php echo escape($car['brand']); ?></h1>

// Vždy prepared statements:
$stmt = $mysqli->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
```

═════════════════════════════════════════════════════

## 🧪 TESTOVANIE BEZPEČNOSTI

### Ako sami testovať:

#### Test 1: SQL Injection
```
V URL bar prejdite na:
http://localhost/projekttt/detail.php?id=1 OR 1=1

Výsledok: ✓ Bez zmien (bezpečné)
Ak by sme nepoužívali prepared statements, vrátilo by všetky autá
```

#### Test 2: XSS - obrázok
```
V admin paneli vytvorte auto s názvom:
<script>alert('XSS')</script>

Výsledok: ✓ Zobrazí sa ako text (bezpečné)
Skript sa nespustí
```

#### Test 3: Session Hijacking
```
Otvoriť DevTools > Application > Cookies
Skopírujte PHPSESSID
Otvorte nový browser (alebo incognito)
Zmeňte PHPSESSID

Výsledok: ✓ Nebudete prihláseného (bezpečné)
Session je viazaná na konkrétny session ID
```

═════════════════════════════════════════════════════

## 📚 ĎALŠIE BEZPEČNOSTNÉ OPATRENIA (pre produkciu)

1. **HTTPS / SSL Certificate**
   - Šifrovanie dát pri prenose
   - Ochrana hesiel a cookies

2. **CSRF Protection (Cross-Site Request Forgery)**
   - Tokens v formulároch
   - Overenie Referer headeru

3. **Rate Limiting**
   - Limit počtu pokusov na login
   - Ochrana pred bruteforce

4. **Logging a Monitoring**
   - Zaznamenávanie podozrivých aktivít
   - IP blocking pre útoky

5. **Regular Updates**
   - PHP na najnovšej verzii
   - MySQL patche
   - Dependencies updatey

6. **Web Application Firewall (WAF)**
   - Cloudflare, AWS WAF
   - Automatická detekcia útokov

═════════════════════════════════════════════════════

## 🎓 CHEAT SHEET - Bezpečný PHP

```php
// ✓ SPRÁVNE
$stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();

// ❌ NESPRÁVNE
$result = $mysqli->query("SELECT * FROM users WHERE id = " . $_GET['id']);

// ✓ SPRÁVNE
<h1><?php echo escape($_GET['title']); ?></h1>

// ❌ NESPRÁVNE
<h1><?php echo $_GET['title']; ?></h1>

// ✓ SPRÁVNE
$hash = password_hash($password, PASSWORD_BCRYPT);
if (password_verify($input, $hash)) { }

// ❌ NESPRÁVNE
if ($input === $stored_password) { }

// ✓ SPRÁVNE
session_start();
if (isset($_SESSION['authenticated'])) { }

// ❌ NESPRÁVNE
if (isset($_COOKIE['user_id'])) { }

// ✓ SPRÁVNE
if ($file_size <= MAX_FILE_SIZE && in_array($ext, ALLOWED_EXTENSIONS)) {
    move_uploaded_file($tmp, UPLOAD_PATH . $new_name);
}

// ❌ NESPRÁVNE
move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name']);
```

═════════════════════════════════════════════════════

## 📝 ZÁVER

Bezpečnosť je:
1. Povinnosť voči používateľom
2. Ochrana vašej aplikácie
3. Právny požiadavok (GDPR, atď.)

Počas vývoja tohto projektu:
✓ Všetky SQL dotazy sú prepared
✓ Všetok output je escapovaný
✓ Heslo je hashované (bcrypt)
✓ Sessions sú bezpečne riadené
✓ File upload je validovaný

Vďaka tomu je aplikácia bezpečná pre produkciu!
