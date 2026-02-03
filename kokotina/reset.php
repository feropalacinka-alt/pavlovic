<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Databázy - Autobazár</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f3f4f6;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 { color: #dc2626; }
        .warning {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin: 10px 5px 10px 0;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        .button-danger {
            background: #dc2626;
            color: white;
        }
        .button-danger:hover {
            background: #b91c1c;
        }
        .button-secondary {
            background: #d1d5db;
            color: #1f2937;
        }
        .button-secondary:hover {
            background: #b6bcc4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Reset Databázy</h1>
        
        <div class="warning">
            <strong>Pozor!</strong> Táto akcia vymaže všetky údaje z databázy autobazar:
            <ul>
                <li>Všetky autá budú vymazané</li>
                <li>Všetky objednávky budú vymazané</li>
                <li>Obrázky ostanú (manuálne ich vymazať)</li>
            </ul>
            Vzorové dáta budú znova vložené.
        </div>

        <?php
        $action = $_GET['confirm'] ?? '';
        
        if ($action === 'yes') {
            $mysqli = @new mysqli("localhost", "root", "");
            
            if ($mysqli->connect_error) {
                echo '<div class="warning">❌ MySQL nie je dostupný</div>';
            } else {
                $mysqli->query("DROP DATABASE IF EXISTS autobazar");
                $mysqli->query("CREATE DATABASE IF NOT EXISTS autobazar");
                $mysqli->select_db("autobazar");
                
                // Zavolaj database.php na inicializáciu
                ob_start();
                include __DIR__ . '/database.php';
                $init_output = ob_get_clean();
                
                $mysqli->close();
                
                echo '<h2 style="color: #16a34a;">✅ Databáza bola resetovaná!</h2>';
                echo '<p>Všetky tabuľky boli vymazané a znova vytvorené s vzorovou údajmi.</p>';
                echo '<p><a href="install.html" class="button button-secondary">← Späť na Setup</a></p>';
            }
        } else {
            ?>
            <p>Ste si istý/á, že chcete resetovať databázu?</p>
            <p>
                <button onclick="window.location.href='?confirm=yes'" class="button button-danger">
                    ✓ Áno, zresetovať
                </button>
                <button onclick="history.back()" class="button button-secondary">
                    ✗ Zrušiť
                </button>
            </p>
            <?php
        }
        ?>
    </div>
</body>
</html>
