<?php
/**
 * Setup skript na inicializáciu databázy
 * Otvorte: http://localhost/projekttt/setup.php
 */

echo "<h1>🚗 AUTOBAZÁR - Setup</h1>";

// Kontrola MySQL
$mysqli = @new mysqli("localhost", "root", "");

if ($mysqli->connect_error) {
    echo "<h2>❌ Chyba: MySQL server nespustený</h2>";
    echo "<p>Spustite MySQL v XAMPP Control Panel</p>";
    die();
}

echo "<h2>✅ MySQL je pripojený</h2>";

// Vytvorenie databázy
$sql_create_db = "CREATE DATABASE IF NOT EXISTS autobazar";
if ($mysqli->query($sql_create_db)) {
    echo "<p>✅ Databáza 'autobazar' vytvorená/existuje</p>";
} else {
    echo "<p>❌ Chyba pri vytváraní databázy: " . $mysqli->error . "</p>";
    die();
}

// Výber databázy
$mysqli->select_db("autobazar");
$mysqli->close();

// Zavolaj database.php skript ktorý inicializuje všetko
include __DIR__ . '/database.php';

echo "<h2>✅ Setup úspešný!</h2>";
echo "<p><a href='index.php'>👉 Prejsť na domov</a></p>";
echo "<p><a href='admin.php'>👉 Prejsť do adminu (admin/admin123)</a></p>";
