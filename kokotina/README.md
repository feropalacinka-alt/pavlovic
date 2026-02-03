# 🚗 AUTOBAZÁR - Webová Aplikácia

Kompletnú webovú aplikáciu autobazáru vytvorenú v PHP, HTML, CSS a MySQL **bez frameworkov**.

---

## 📋 Obsah

1. [Štruktúra projektu](#-štruktúra-projektu)
2. [Technológie](#-technológie)
3. [Databáza](#-databáza)
4. [Inštalácia a spustenie](#-inštalácia-a-spustenie)
5. [Funkcionality](#-funkcionality)
6. [Admin prihláška](#-admin-prihláška)
7. [Bezpečnosť](#-bezpečnosť)
8. [Testovacia údajá](#-testovacia-údajá)

---

## 📁 Štruktúra projektu

```
projekttt/
├── inc/
│   ├── config.php          # Globálna konfigurácia a session
│   └── db.php              # Databázové pripojenie a funkcie
├── uploads/                # Priečinok pre nahrané obrázky
├── index.php               # Úvodná stránka so zoznamom áut
├── detail.php              # Detail konkrétneho auta
├── cart.php                # Nákupný košík
├── checkout.php            # Simulácia platby kartou
├── admin.php               # Admin panel (pridávanie, úprava, mazanie áut)
├── style.css               # CSS štýly (Flexbox + Grid, responzívny)
├── database.php            # PHP skript na inicializáciu databázy (AUTOMATICKY)
└── README.md               # Táto dokumentácia
```

### Popis súborov:

| Súbor | Popis |
|-------|-------|
| **index.php** | Zobrazuje zoznam všetkých áut v GridLayout |
| **detail.php** | Detailný pohľad na auto (viac fotiek, tech. parametre) |
| **cart.php** | Nákupný košík s možnosťou odobrania áut |
| **checkout.php** | Platobný formulár (simulácia, bez reálnej brány) |
| **admin.php** | Admin panel na správu áut (CRUD) |
| **style.css** | Moderný CSS s responsívnym dizajnom |
| **database.php** | PHP skript na automatickú inicializáciu databázy |

---

## 🛠️ Technológie

### Backend:
- **PHP 7.0+** (procedurálny PHP s MySQLi)
- **MySQL** (databáza - externa, na db.r6.websupport.sk)
- **Prepared Statements** (ochrana pred SQL injection)

### Frontend:
- **HTML5** (sémantické značky)
- **CSS3** (Flexbox, CSS Grid, animácie)
- **JavaScript** (vanilla JS - bez frameworkov)

### Výhody prístupu:
- ✅ **Bez závislostí** - žiadne frameworky
- ✅ **Rýchly** - minimálny overhead
- ✅ **Bezpečný** - prepared statements
- ✅ **Jednoduchý** - ľahko sa upravuje
- ✅ **Responzívny** - funguje na mobiloch, tabletoch a počítačoch
- ✅ **Externá databáza** - bez potreby lokálneho MySQL serveru

---

## 🗄️ Databáza

### Štruktúra tabuliek:

#### 1. `cars` - Tabuľka áut
```sql
- id (INT, PRIMARY KEY)
- brand (VARCHAR 100) - značka auta
- model (VARCHAR 100) - model auta
- year (INT) - rok výroby
- price (DECIMAL 10,2) - cena v €
- description (TEXT) - popis auta
- image_url (VARCHAR 255) - URL hlavného obrázku
- engine_type (VARCHAR 50) - typ motora (Benzín, Diesel...)
- fuel_type (VARCHAR 50) - druh paliva
- transmission (VARCHAR 50) - prevodovka (Manuálna, Automatická)
- power (INT) - výkon v kW
- mileage (INT) - najazdené km
- color (VARCHAR 50) - farba vozidla
- created_at (TIMESTAMP) - dátum vytvorenia
- updated_at (TIMESTAMP) - dátum poslednej úpravy
```

#### 2. `car_images` - Obrázky áut
```sql
- id (INT, PRIMARY KEY)
- car_id (INT, FOREIGN KEY) - odkaz na auto
- image_url (VARCHAR 255) - cesta k obrázku
- is_main (BOOLEAN) - či je to hlavný obrázok
- uploaded_at (TIMESTAMP) - dátum nahratia
```

#### 3. `orders` - Objednávky
```sql
- id (INT, PRIMARY KEY)
- order_number (VARCHAR 50, UNIQUE) - číslo objednávky
- cardholder_name (VARCHAR 100) - meno držiteľa karty
- total_price (DECIMAL 10,2) - celková cena
- status (VARCHAR 50) - stav objednávky (completed, pending...)
- created_at (TIMESTAMP) - dátum objednávky
```

#### 4. `order_items` - Položky objednávky
```sql
- id (INT, PRIMARY KEY)
- order_id (INT, FOREIGN KEY) - odkaz na objednávku
- car_id (INT, FOREIGN KEY) - odkaz na auto
- brand (VARCHAR 100) - značka auta
- model (VARCHAR 100) - model auta
- price (DECIMAL 10,2) - cena v čase nákupu
```

#### 5. `admin_users` - Administrátori
```sql
- id (INT, PRIMARY KEY)
- username (VARCHAR 50, UNIQUE) - používateľské meno
- password (VARCHAR 255) - hashované heslo (bcrypt)
- created_at (TIMESTAMP) - dátum vytvorenia
```

---

## 🚀 Inštalácia a spustenie

### Krok 1: Príprava prostredia

**🗄️ Údaje k databáze:**
- **Databázový server:** db.r6.websupport.sk
- **Port:** 3306
- **Meno databázy:** auto_demo
- **Používateľ:** ziak_1
- **Heslo:** 8ggVKh<KYUe2]<OuJ4xq

**🌐 Lokálny webový server (XAMPP):**
1. **Stiahnutie XAMPP**: [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. **Inštalácia** štandardným spôsobom
3. **Spustenie**: Otvoriť XAMPP Control Panel a spustiť:
   - Apache (webový server)

### Krok 2: Umiestnenie súborov

Všetky súbory projektu sú už v priečinku:
```
C:\xampp\htdocs\projekttt\
```

### Krok 3: Test pripojenia k databáze

1. Otvoriť webový prehliadač
2. Zadať URL: **[http://localhost/projekttt/database.php](http://localhost/projekttt/database.php)**
3. Stránka otestuje pripojenie a zobrazí:
   - ✅ Stav pripojenia
   - 📋 Zoznam tabuliek
   - 📊 Počet záznamov v tabuľkách

### Krok 4: Spustenie aplikácie

1. Zadať URL: **[http://localhost/projekttt](http://localhost/projekttt)**

**Hotovo!** Aplikácia je spustená.

---

## ✨ Funkcionality

### 👤 Užívateľ (Verejná stránka)

#### 🏠 Domovská stránka (`index.php`)
- Zobrazuje všetky dostupné autá
- Grid layout s kartami
- Informácie:
  - Obrázok auta
  - Značka a model
  - Rok výroby
  - Najazdené km
  - Farba
  - Cena
- **Tlačidlo "Do košíka"** - pridá auto do nákupného košíka
- **Tlačidlo "Detail"** - prejde na detailnú stránku

#### 🔍 Detail auta (`detail.php`)
- Veľké zobrazenie obrázku (s možnosťou klikania na miniatúry)
- Technické parametre:
  - Typ motora
  - Palivo
  - Prevodovka
  - Výkon (kW)
  - Najazdené km
  - Farba
- Kompletný popis auta
- Cena
- Tlačidlo "Pridať do košíka"

#### 🛒 Nákupný košík (`cart.php`)
- Tabuľka s vybraným autami
- Zobrazuje:
  - Názov auta
  - Rok výroby
  - Cenu za kus
- Možnosť **odobrania auta** z košíka
- **Celková cena** objednávky
- Tlačidlo **"Pokračovať k platbe"**
- Tlačidlo **"Pokračovať v nákupe"**

#### 💳 Checkout / Simulácia platby (`checkout.php`)
- Bezpečný platobný formulár
- Polia:
  - Meno držiteľa karty ✓
  - Číslo karty (16 číslic) ✓
  - Dátum platnosti (MM/YY) ✓
  - CVV (3-4 číslice) ✓
- **Validácia formulára** (JavaScript + PHP)
- Skúšobné karty:
  - `4111111111111111` (Visa)
  - `5555555555554444` (Mastercard)
- Po úspešnej **"platbe"**:
  - Zobrazenie potvrdenia objednávky
  - Číslo objednávky
  - Meno
  - Celková suma
- **SIMULÁCIA PLATBY** - žiadna reálna transakcia!

### 👨‍💼 Admin (`admin.php`)

#### Prihláška
- Bezpečné prihlasovanie
- Session management

#### Správa áut (CRUD)

**CREATE** - Pridať nové auto
- Formulár na zadanie všetkých údajov
- Upload obrázkov (viacero)
- Validácia vstupných údajov

**READ** - Zobrazenie áut
- Zoznam všetkých áut
- Zobrazenie obrázkov
- Rýchly náhľad parametrov

**UPDATE** - Úprava auta
- Modálne okno s formulárom
- Zmena ľubovoľného údaja
- Pridanie ďalších obrázkov

**DELETE** - Mazanie auta
- Potvrdenie pri mazaní
- Vymazanie všetkých asociovaných obrázkov
- Vymazanie z databázy

---

## 🔐 Admin prihláška

### Testovací účet:
```
Meno: admin
Heslo: admin123
```

### Zmena hesla:
Heslo je uložené ako **bcrypt hash**. Na zmenu:

1. Vytvoriť nový hash pomocou:
```php
<?php
echo password_hash('tvoje_heslo', PASSWORD_BCRYPT);
?>
```

2. Nahradiť v súbore `inc/config.php`:
```php
define('ADMIN_PASSWORD_HASH', 'new_hash_here');
```

---

## 🔒 Bezpečnosť

### Implementované opatrenia:

#### 1. **SQL Injection ochrana**
```php
// ✅ BEZPEČNÉ - Prepared Statements
$stmt = $mysqli->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
```

#### 2. **Cross-Site Scripting (XSS) ochrana**
```php
// Všetok output je escapovaný:
echo escape($user_input);
// ktorý volá: htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
```

#### 3. **Heslá**
```php
// Hashovanie hesiel cez bcrypt
password_hash('heslo', PASSWORD_BCRYPT);
password_verify('heslo', $hash);
```

#### 4. **Session management**
```php
session_start();
// Každý admin je overený cez session
```

#### 5. **File Upload validácia**
- Kontrola veľkosti súboru (max 5MB)
- Kontrola rozšírenia (jpg, png, gif)
- Kontrola MIME typu

---

## 📝 Testovacia údajá

Databáza je automaticky naplnená **5 vzorými autami**:

| Značka | Model | Rok | Cena | Km |
|--------|-------|-----|------|-----|
| Volkswagen | Golf | 2020 | 15 000 € | 45 000 km |
| BMW | 3 Series | 2019 | 18 000 € | 52 000 km |
| Mercedes-Benz | C-Class | 2021 | 22 000 € | 28 000 km |
| Toyota | Corolla | 2018 | 12 000 € | 65 000 km |
| Audi | A4 | 2020 | 19 500 € | 38 000 km |

---

## 🎨 CSS Charakteristiky

- **Flexbox + CSS Grid** - moderný layout
- **Responzívny dizajn** - funguje na všetkých veľkostiach obrazoviek
- **CSS Premenné** - ľahká zmena farieb a štýlov
- **Animácie** - hladké prechody a efekty
- **Breakpointy** pre:
  - Desktopy (1200px+)
  - Tablety (768px)
  - Mobilné (480px)

---

## 🐛 Riešenie problémov

### Problem: "Chyba pripojenia k databáze"
**Riešenie:**
1. Skontrolujte dostupnosť serveru db.r6.websupport.sk
2. Skontrolujte internú konektivitu (port 3306 musí byť otvorený)
3. Skontrolujte prihlasovacie údaje v `inc/db.php`:
   - Server: `db.r6.websupport.sk`
   - Používateľ: `ziak_1`
   - Databáza: `auto_demo`
4. Skúste test pripojenia na [database.php](http://localhost/projekttt/database.php)

### Problem: "Obrázky sa neukazujú"
**Riešenie:**
1. Skontrolujte, či existuje priečinok `/uploads`
2. Skontrolujte oprávnenia priečinka (chmod 755)
3. Skontrolujte URL cestu v kóde

### Problem: "Admin sa nedá prihlásiť"
**Riešenie:**
1. Skontrolujte, či je tabuľka `admin_users` v databáze na [database.php](http://localhost/projekttt/database.php)
2. Vyčistite cookies a cache prehliadača
3. Skúste nové heslo
4. Overujte heslo: `admin123` pre používateľa `admin`

---

## 📞 Technické údajy

- **PHP verzia**: 7.0+
- **MySQL verzia**: 5.7+
- **Prehliadač**: Všetky moderné prehliadače
- **Veľkosť projektu**: ~100KB
- **Čas načítania**: <1 sekunda

---

## 📚 Komentovaný kód

Všetok kód je dobre komentovaný pomocou:
```php
/**
 * Funkcia na výpočet ceny
 * @param float $price - cena v € 
 * @return string - formátovaná cena
 */
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}
```

---

## 🎓 Zdrojový kód - Štruktúra

### Procedurálny PHP
- Všetok kód je napísaný **procedurálne** (nie OOP)
- Ľahšie na pochopenie pre začiatočníkov
- Stále bezpečný s prepared statements
- Môžete ľahko rozšíriť na OOP

### Súbory a funkcionalita:

**inc/db.php:**
- `executeSafeQuery()` - bezpečný SELECT
- `insertData()` - bezpečný INSERT
- `updateData()` - bezpečný UPDATE
- `deleteData()` - bezpečný DELETE

**inc/config.php:**
- `setFlashMessage()` - flash správy
- `getFlashMessage()` - načítanie správ
- `formatPrice()` - formátovanie cien
- `escape()` - XSS ochrana
- `generateOrderNumber()` - čísla objednávok

---

## ✅ Kontrolný zoznam funkcionalít

- [x] Úvodná stránka so zoznamom áut
  - [x] Obrázok auta
  - [x] Základné údaje
  - [x] Cena
  - [x] Tlačidlo "Pridať do košíka"
- [x] Detail auta
  - [x] Viacero fotiek
  - [x] Technické parametre
  - [x] Popis
  - [x] Cena
- [x] Košík
  - [x] Zoznam vybraných áut
  - [x] Možnosť odobrania
  - [x] Celková cena
  - [x] Tlačidlo na pokračovanie
- [x] Platba (simulácia)
  - [x] Formulár na zadanie údajov
  - [x] Validácia
  - [x] Potvrdenie objednávky
- [x] Admin sekcia
  - [x] Pridávanie áut
  - [x] Úprava áut
  - [x] Mazanie áut
  - [x] Upload obrázkov
- [x] Bezpečnosť
  - [x] SQL Injection ochrana
  - [x] XSS ochrana
  - [x] Hashovanie hesiel
  - [x] Session management
- [x] Dizajn
  - [x] Moderný CSS
  - [x] Flexbox / Grid
  - [x] Responzívny
  - [x] Komentovaný kód

---

## 🚀 Čo ďalej?

Tipy na rozšírenie:
1. **PayPal integrácia** - namiesto simulácie
2. **Email notifikácie** - pri objednávkach
3. **Fulltextové vyhľadávanie** - na auta
4. **Filtre a triedenie** - podľa ceny, roku...
5. **Hodnotenia a komentáre** - od zákazníkov
6. **Login pre zákazníkov** - história nákupov
7. **Fotogaléria** - s lightbox efektami
8. **Multi-language** - SK/EN/DE

---

## 📄 Licencia

Voľne dostupný projekt - používajte ako chcete!

---

## 👨‍💻 Podpora a kontakt

Ak máte otázky alebo problémy:
1. Skontrolujte sekciu "Riešenie problémov"
2. Skontrolujte konzolu prehliadača (F12)
3. Skontrolujte logy MySQL

---

**Ďakujem za používanie našej aplikácie! 🎉**

Vytvorené: 27. január 2026
