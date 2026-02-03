<?php
/**
 * 🗄️ MySQL Databázové Pripojenie
 * Šablóna na napojenie sa na existujúcu MySQL databázu
 */

// ═══════════════════════════════════════════════════════════════
// ⚙️ KONFIGURÁCIA - DOPLŇTE SVOJE ÚDAJE
// ═══════════════════════════════════════════════════════════════

// Údaje k MySQL serveru

$servername = "db.r6.websupport.sk:3306";      // 🔹 Adresa serveru (zvyčajne localhost)
$username = "ziak_1";             // 🔹 MySQL používateľ
$password = "8ggVKh<KYUe2]<OuJ4xq";                 // 🔹 MySQL heslo (XAMPP štandardne prázdne)
$dbname = "auto_demo";          // 🔹 Meno vašej databázy

// ═════════════════════════════════════════════════════════════====
// 🔗 PRIPOJENIE NA DATABÁZU
// ═══════════════════════════════════════════════════════════════

try {
    // Vytvorenie MySQLi objektu
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    
    // Kontrola chyby pripojenia
    if ($mysqli->connect_error) {
        throw new Exception("❌ Chyba pripojenia: " . $mysqli->connect_error);
    }
    
    // Nastavenie UTF-8 kódovania
    $mysqli->set_charset("utf8mb4");
    
    // ✅ Pripojenie úspešné!
    echo "✅ Pripojenie na databázu je OK!<br>";
    echo "📊 Databáza: <strong>" . $dbname . "</strong><br>";
    echo "👤 Používateľ: <strong>" . $username . "</strong><br>";
    
} catch (Exception $e) {
    // Ak nastane chyba
    die("
        <h2>⚠️ Chyba pri pripojení</h2>
        <p><strong>Problém:</strong> " . $e->getMessage() . "</p>
        <p><strong>Údaje o databáze:</strong></p>
        <ul>
            <li>Server: db.r6.websupport.sk:3306</li>
            <li>Databáza: auto_demo</li>
            <li>Používateľ: ziak_1</li>
        </ul>
        <p><strong>Možné riešenia:</strong></p>
        <ul>
            <li>1. Skontrolujte dostupnosť serveru db.r6.websupport.sk</li>
            <li>2. Skontrolujte internú konektivitu na port 3306</li>
            <li>3. Overujte prihlasovací údaje (ziak_1 / heslo)</li>
            <li>4. Uistite sa, že databáza 'auto_demo' existuje na serveri</li>
        </ul>
        <hr>
        <p><a href='README.md'>📖 Vrátiť sa na README</a></p>
    ");
}

// ═══════════════════════════════════════════════════════════════
// 🧪 TEST - Vylistovanie tabuliek
// ═══════════════════════════════════════════════════════════════

$result = $mysqli->query("SHOW TABLES");

if ($result && $result->num_rows > 0) {
    echo "<br><h3>📋 Tabuľky v databáze:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>✓ " . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<br><p>⚠️ V databáze žiadne tabuľky. Importujte complete.sql cez phpMyAdmin.</p>";
}

// ═══════════════════════════════════════════════════════════════
// 📊 TEST - Počet záznamov
// ═══════════════════════════════════════════════════════════════

$tables_to_check = ['cars', 'orders', 'admin_users', 'car_images', 'order_items'];

echo "<br><h3>📊 Počet záznamov:</h3>";
echo "<ul>";
foreach ($tables_to_check as $table) {
    $check = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
    if ($check) {
        $row = $check->fetch_assoc();
        echo "<li><strong>" . ucfirst($table) . ":</strong> " . $row['cnt'] . " záznamov</li>";
    }
}
echo "</ul>";

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>✅ Databáza je správne nastavená!</p>";

// Zatvorenie pripojenia (nepovinné, PHP to zatvorí automaticky)
// $mysqli->close();

?>

