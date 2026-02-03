-- ═══════════════════════════════════════════════════════════════
-- 🚗 AUTOBAZÁR - KOMPLETNÝ SQL SKRIPT
-- ═══════════════════════════════════════════════════════════════
-- ČISTÝ, FUNKČNÝ SQL - BEZPEČNÝ NA IMPORT
-- Vytvorenie databázy, tabuliek a vzorových dát

-- ═══════════════════════════════════════════════════════════════
-- KROK 1: VYTVORENIE DATABÁZY
-- ═══════════════════════════════════════════════════════════════

DROP DATABASE IF EXISTS auto_demo;
CREATE DATABASE auto_demo 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_general_ci;

USE auto_demo;

-- ═══════════════════════════════════════════════════════════════
-- TABUĽKA 1: cars (Autá v bazári)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE cars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    engine_type VARCHAR(50),
    fuel_type VARCHAR(50),
    transmission VARCHAR(50),
    power INT,
    mileage INT,
    color VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_price (price),
    INDEX idx_brand (brand)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- TABUĽKA 2: car_images (Obrázky áut)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE car_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    car_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    INDEX idx_car_id (car_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- TABUĽKA 3: users (Registrovaní používatelia)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- TABUĽKA 4: admin_users (Administrátori)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- TABUĽKA 5: orders (Objednávky)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    cardholder_name VARCHAR(100) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_number (order_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- TABUĽKA 6: order_items (Položky v objednávkach)
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    car_id INT,
    brand VARCHAR(100),
    model VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_car_id (car_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ═══════════════════════════════════════════════════════════════
-- VLOŽENIE VZOROVÝCH DÁT
-- ═══════════════════════════════════════════════════════════════

-- Admin užívateľ
-- Username: admin
-- Password: admin123 (bcrypt hashed)
INSERT INTO admin_users (username, password) 
VALUES ('admin', '$2y$10$YIjlrDxwucVcAe8H5LBQ2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMzm');

-- 5 Vzorových áut s obrázkami z imgs/ priečinka
INSERT INTO cars (brand, model, year, price, description, image_url, engine_type, fuel_type, transmission, power, mileage, color) 
VALUES 
    ('Volkswagen', 'Golf', 2020, 15000.00, 'Spoľahlivé a spotrebiteľsky ekonomické vozidlo. Výborný stav, komplexný servis, nový set pneumatík.', 'imgs/golf.jpg', 'Benzín', 'Benzín', 'Manuálna', 115, 45000, 'Čierna'),
    ('BMW', '3 Series', 2019, 18000.00, 'Luxusné vozidlo s moderným vybavením. Adaptívny tempomat, panorámna strecha, kúrená sedadlá, navigácia.', 'imgs/bmw.jpg', 'Diesel', 'Diesel', 'Automatická', 150, 52000, 'Strieborná'),
    ('Mercedes-Benz', 'C-Class', 2021, 22000.00, 'Premium vozidlo s technológiami budúcnosti. Nápravný asistent, multimediálny systém, asistent parkingu.', 'imgs/mercedes.jpg', 'Benzín', 'Benzín', 'Automatická', 180, 28000, 'Biela'),
    ('Toyota', 'Corolla', 2018, 12000.00, 'Japonská kvalita a spoľahlivosť. Skúšaný model s výborným renomé, ideálne pre rodinný transport.', 'imgs/toyota.jpg', 'Benzín', 'Benzín', 'Manuálna', 110, 65000, 'Modrá'),
    ('Audi', 'A4', 2020, 19500.00, 'Výkonné vozidlo s progresívnym dizajnom. Asistent parkovania, hlasové ovládanie, panorámna strecha.', 'imgs/audi.jpg', 'Diesel', 'Diesel', 'Automatická', 163, 38000, 'Šedá');

-- Obrázky áut v galérií
-- Každé auto má svoj obrázok označený ako hlavný (is_main = 1)
INSERT INTO car_images (car_id, image_url, is_main) 
VALUES 
    (1, 'imgs/golf.jpg', 1),
    (2, 'imgs/bmw.jpg', 1),
    (3, 'imgs/mercedes.jpg', 1),
    (4, 'imgs/toyota.jpg', 1),
    (5, 'imgs/audi.jpg', 1);

-- Vzorové objednávky
INSERT INTO orders (order_number, cardholder_name, total_price, status)
VALUES 
    ('ORD-2026-001', 'Ján Varga', 15000.00, 'completed'),
    ('ORD-2026-002', 'Mária Horváthová', 22000.00, 'completed'),
    ('ORD-2026-003', 'Peter Novák', 18000.00, 'pending');

-- Položky v objednávkach
INSERT INTO order_items (order_id, car_id, brand, model, price)
VALUES 
    (1, 1, 'Volkswagen', 'Golf', 15000.00),
    (2, 3, 'Mercedes-Benz', 'C-Class', 22000.00),
    (3, 2, 'BMW', '3 Series', 18000.00);

-- ═══════════════════════════════════════════════════════════════
-- ✅ DATABÁZA JE HOTOVÁ
-- ═══════════════════════════════════════════════════════════════
-- Vytvorené:
-- ✓ 6 tabuliek (cars, car_images, users, admin_users, orders, order_items)
-- ✓ 5 vzorových áut
-- ✓ Všetky obrázky pointing na imgs/ priečinok
-- ✓ User tabuľka pripravená na registráciu
-- ✓ Admin účet (admin/admin123)
-- ═══════════════════════════════════════════════════════════════
