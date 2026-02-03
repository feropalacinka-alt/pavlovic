-- ═══════════════════════════════════════════════════════════════
-- 🚗 AUTOBAZÁR - BEZPEČNÝ IMPORT (bez DROP DATABASE)
-- ═══════════════════════════════════════════════════════════════

USE auto_demo;

-- VYČISTENIE STARÝCH DÁT (bez vymazávania databázy)
TRUNCATE TABLE order_items;
TRUNCATE TABLE car_images;
TRUNCATE TABLE orders;
TRUNCATE TABLE cars;
TRUNCATE TABLE users;
TRUNCATE TABLE admin_users;

-- ═══════════════════════════════════════════════════════════════
-- VLOŽENIE DÁT
-- ═══════════════════════════════════════════════════════════════

-- Admin užívateľ (heslo: admin123)
INSERT INTO admin_users (username, password) 
VALUES ('admin', '$2y$10$YIjlrDxwucVcAe8H5LBQ2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMzm');

-- 5 VZOROVÝCH ÁUT S SPRÁVNYMI CESTAMI K OBRÁZKOM
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
        'imgs/toyota.jpg', 
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

-- OBRÁZKY V GALÉRIÍ (všetky zo správneho priečinka imgs/)
INSERT INTO car_images (car_id, image_url, is_main) 
VALUES 
    (1, 'imgs/golf.jpg', TRUE),
    (2, 'imgs/bmw.jpg', TRUE),
    (3, 'imgs/mercedes.jpg', TRUE),
    (4, 'imgs/toyota.jpg', TRUE),
    (5, 'imgs/audi.jpg', TRUE);

-- VZOROVÉ OBJEDNÁVKY
INSERT INTO orders (order_number, cardholder_name, total_price, status)
VALUES 
    ('ORD-2025-001', 'Ján Varga', 15000.00, 'completed'),
    ('ORD-2025-002', 'Mária Horváthová', 22000.00, 'completed'),
    ('ORD-2025-003', 'Peter Novák', 18000.00, 'pending');

-- POLOŽKY V OBJEDNÁVKACH
INSERT INTO order_items (order_id, car_id, brand, model, price)
VALUES 
    (1, 1, 'Volkswagen', 'Golf', 15000.00),
    (2, 3, 'Mercedes-Benz', 'C-Class', 22000.00),
    (3, 2, 'BMW', '3 Series', 18000.00);

-- ═══════════════════════════════════════════════════════════════
-- ✅ HOTOVO!
-- ═══════════════════════════════════════════════════════════════
