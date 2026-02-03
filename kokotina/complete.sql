-- ═══════════════════════════════════════════════════════════════
-- 🚗 AUTOBAZÁR - KOMPLETNÝ MySQL KÓD
-- ═══════════════════════════════════════════════════════════════
-- Skript na vytvorenie celej databázy s tabuľkami a dátami

-- Vymazať staru databázu (ak existuje)
DROP DATABASE IF EXISTS auto_demo;

-- Vytvorenie novej databázy
CREATE DATABASE IF NOT EXISTS auto_demo;
USE auto_demo;

-- ═══════════════════════════════════════════════════════════════
-- 1. TABUĽKA: cars (Autá)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE cars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    brand VARCHAR(100) NOT NULL COMMENT 'Značka auta (VW, BMW, Mercedes...)',
    model VARCHAR(100) NOT NULL COMMENT 'Model auta (Golf, 3 Series...)',
    year INT NOT NULL COMMENT 'Rok výroby',
    price DECIMAL(10, 2) NOT NULL COMMENT 'Cena v EUR',
    description TEXT COMMENT 'Dlhý popis vozidla',
    image_url VARCHAR(255) COMMENT 'URL na hlavný obrázok',
    engine_type VARCHAR(50) COMMENT 'Typ motora (Benzín, Diesel, Hybrid...)',
    fuel_type VARCHAR(50) COMMENT 'Druh paliva',
    transmission VARCHAR(50) COMMENT 'Typ prevodovky (Manuálna, Automatická)',
    power INT COMMENT 'Výkon v kW',
    mileage INT COMMENT 'Najazdené kilometre',
    color VARCHAR(50) COMMENT 'Farba vozidla',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum vytvorenia záznamu',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Dátum poslednej úpravy'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabuľka všetkých áut v bazári';

-- Index na cenu (pre rýchlejšie vyhľadávanie)
CREATE INDEX idx_price ON cars(price);
CREATE INDEX idx_brand ON cars(brand);

-- ═══════════════════════════════════════════════════════════════
-- 2. TABUĽKA: car_images (Obrázky áut)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE car_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    car_id INT NOT NULL COMMENT 'Odkaz na auto',
    image_url VARCHAR(255) NOT NULL COMMENT 'Cesta/URL k obrázku',
    is_main BOOLEAN DEFAULT FALSE COMMENT 'Či je to hlavný obrázok',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum nahratia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Galéria obrázkov pre každé auto';

-- Add FK separately to avoid inline COMMENT issues
ALTER TABLE car_images
    ADD CONSTRAINT fk_car_images_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE;

-- Index na car_id
CREATE INDEX idx_car_id ON car_images(car_id);

-- ═══════════════════════════════════════════════════════════════
-- 3. TABUĽKA: orders (Objednávky)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL COMMENT 'Unikátne číslo objednávky',
    cardholder_name VARCHAR(100) NOT NULL COMMENT 'Meno držiteľa karty',
    total_price DECIMAL(10, 2) NOT NULL COMMENT 'Celková cena objednávky',
    status VARCHAR(50) DEFAULT 'completed' COMMENT 'Stav objednávky (completed, pending, cancelled...)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum vytvorenia objednávky'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Všetky objednávky od zákazníkov';

-- Index na order_number pre rýchle vyhľadávanie
CREATE INDEX idx_order_number ON orders(order_number);

-- ═══════════════════════════════════════════════════════════════
-- 4. TABUĽKA: order_items (Položky v objednávke)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL COMMENT 'Odkaz na objednávku',
    car_id INT NULL COMMENT 'Odkaz na auto',
    brand VARCHAR(100) COMMENT 'Značka auta (snapshot v čase nákupu)',
    model VARCHAR(100) COMMENT 'Model auta (snapshot v čase nákupu)',
    price DECIMAL(10, 2) NOT NULL COMMENT 'Cena v čase nákupu (môže sa líšiť od aktuálnej)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Jednotlivé autá v každej objednávke';

-- Pridať foreign key constraints separátne (bez inline COMMENT)
ALTER TABLE order_items
    ADD CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_order_items_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL;

-- Index na order_id a car_id
CREATE INDEX idx_order_id_items ON order_items(order_id);
CREATE INDEX idx_car_id_items ON order_items(car_id);

-- ═══════════════════════════════════════════════════════════════
-- 5. TABUĽKA: admin_users (Administrátori)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL COMMENT 'Používateľské meno (unikátne)',
    password VARCHAR(255) NOT NULL COMMENT 'Heslo (bcrypt hash)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum vytvorenia účtu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Admin konta pre správu aplikácie';

-- Index na username
CREATE INDEX idx_username ON admin_users(username);

-- ═══════════════════════════════════════════════════════════════
-- 6. TABUĽKA: users (Používatelia / registrácie)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Zákazníci a používateľské účty';

CREATE INDEX idx_users_email ON users(email);

-- ═══════════════════════════════════════════════════════════════
-- 📊 VLOŽENIE VZOROVÝCH DÁT
-- ═══════════════════════════════════════════════════════════════

-- Admin užívateľ (heslo: admin123, zašifrované cez bcrypt)
INSERT INTO admin_users (username, password) 
VALUES ('admin', '$2y$10$YIjlrDxwucVcAe8H5LBQ2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMzm');

-- Vzorové autá
INSERT INTO cars (brand, model, year, price, description, image_url, engine_type, fuel_type, transmission, power, mileage, color) 
VALUES 
    (
        'Volkswagen', 
        'Golf', 
        2020, 
        15000.00, 
        'Spoľahlivé a spotrebiteľsky ekonomické vozidlo. Výborný stav, komplexný servis, nový set pneumatík.', 
        'imgs/golf.jpg', 
        'Benzín', 
        'Benzín', 
        'Manuálna', 
        115, 
        45000, 
        'Čierna'
    ),
    (
        'BMW', 
        '3 Series', 
        2019, 
        18000.00, 
        'Luxusné vozidlo s moderným vybavením. Adaptívny tempomat, panorámna strecha, kúrená sedadlá, navigácia.', 
        'imgs/bmw.jpg', 
        'Diesel', 
        'Diesel', 
        'Automatická', 
        150, 
        52000, 
        'Strieborná'
    ),
    (
        'Mercedes-Benz', 
        'C-Class', 
        2021, 
        22000.00, 
        'Premium vozidlo s technológiami budúcnosti. Nápravný asistent, multimediálny systém, asistent parkingu.', 
        'imgs/mercedes.jpg', 
        'Benzín', 
        'Benzín', 
        'Automatická', 
        180, 
        28000, 
        'Biela'
    ),
    (
        'Toyota', 
        'Corolla', 
        2018, 
        12000.00, 
        'Japonská kvalita a spoľahlivosť. Skúšaný model s výborným renomé, ideálne pre rodinný transport.', 
        'imgs/corolla.jpg', 
        'Benzín', 
        'Benzín', 
        'Manuálna', 
        110, 
        65000, 
        'Modrá'
    ),
    (
        'Audi', 
        'A4', 
        2020, 
        19500.00, 
        'Výkonné vozidlo s progresívnym dizajnom. Asistent parkovania, hlasové ovládanie, panorámna strecha.', 
        'imgs/audi.jpg', 
        'Diesel', 
        'Diesel', 
        'Automatická', 
        163, 
        38000, 
        'Šedá'
    );

-- Vzorové obrázky áut (na galérií)
INSERT INTO car_images (car_id, image_url, is_main) 
VALUES 
    -- Golf
    (1, 'imgs/golf.jpg', TRUE),
    -- BMW
    (2, 'imgs/bmw.jpg', TRUE),
    -- Mercedes
    (3, 'imgs/mercedes.jpg', TRUE),
    -- Toyota
    (4, 'imgs/corolla.jpg', TRUE),
    -- Audi
    (5, 'imgs/audi.jpg', TRUE);

-- Vzorová objednávka (aby sme mali dáta aj v orders tabuľke)
INSERT INTO orders (order_number, cardholder_name, total_price, status)
VALUES 
    ('ORD-2025-001', 'Ján Varga', 15000.00, 'completed'),
    ('ORD-2025-002', 'Mária Horváthová', 22000.00, 'completed'),
    ('ORD-2025-003', 'Peter Novák', 18000.00, 'pending');

-- Vzorové položky objednávok
INSERT INTO order_items (order_id, car_id, brand, model, price)
VALUES 
    (1, 1, 'Volkswagen', 'Golf', 15000.00),
    (2, 3, 'Mercedes-Benz', 'C-Class', 22000.00),
    (3, 2, 'BMW', '3 Series', 18000.00);

-- ═══════════════════════════════════════════════════════════════
-- ✅ DATABÁZA HOTOVÁ!
-- ═══════════════════════════════════════════════════════════════
-- 
-- Vytvorené tabuľky:
-- ✓ cars (5 áut)
-- ✓ car_images (15 obrázkov)
-- ✓ users (registrácie - prázdna, pripravená na dáta)
-- ✓ orders (3 vzorové objednávky)
-- ✓ order_items (3 vzorové položky)
-- ✓ admin_users (1 admin: admin/admin123)
--
-- Admin login:
--   Meno: admin
--   Heslo: admin123
--
-- Tabuľky sú optimalizované s:
-- ✓ Správne dátové typy
-- ✓ Foreign keys s ON DELETE CASCADE/SET NULL
-- ✓ Indexy na najčastejšie vyhľadávané polia
-- ✓ UTF-8 kódovanie
-- ✓ Timestamps pre dátumy
-- ✓ UNIQUE email v users tabuľke
--
-- ═══════════════════════════════════════════════════════════════
