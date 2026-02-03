#!/bin/bash
# Skript na rýchlu inicializáciu databázy

mysql -u root << EOF
CREATE DATABASE IF NOT EXISTS autobazar;
USE autobazar;

-- Tabuľka pre autá
CREATE TABLE IF NOT EXISTS cars (
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabuľka pre fotky áut
CREATE TABLE IF NOT EXISTS car_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    car_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Tabuľka pre objednávky
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    cardholder_name VARCHAR(100) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabuľka pre položky objednávky
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    car_id INT NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL
);

-- Tabuľka pre admin
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vloženie vzorového admina (heslo: admin123)
INSERT INTO admin_users (username, password) 
VALUES ('admin', '$2y$10$YIjlrDxwucVcAe8H5LBQ2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMzm');

-- Vzorové dáta - autá
INSERT INTO cars (brand, model, year, price, description, image_url, engine_type, fuel_type, transmission, power, mileage, color) VALUES
('Volkswagen', 'Golf', 2020, 15000, 'Spoľahlivé a spotrebiteľsky ekonomické vozidlo. Výborný stav, komplexný servis.', 'uploads/golf.jpg', 'Benzín', 'Benzín', 'Manuálna', 115, 45000, 'Čierna'),
('BMW', '3 Series', 2019, 18000, 'Luxusné vozidlo s moderným vybavením. Adaptívny tempomat, panorámna strecha.', 'uploads/bmw.jpg', 'Diesel', 'Diesel', 'Automatická', 150, 52000, 'Strieborná'),
('Mercedes-Benz', 'C-Class', 2021, 22000, 'Premium vozidlo s technológiami budúcnosti. Nápravný asistent, multimediálny systém.', 'uploads/mercedes.jpg', 'Benzín', 'Benzín', 'Automatická', 180, 28000, 'Biela'),
('Toyota', 'Corolla', 2018, 12000, 'Japonská kvalita a spoľahlivosť. Skúšaný model s výborným renomé.', 'uploads/corolla.jpg', 'Benzín', 'Benzín', 'Manuálna', 110, 65000, 'Modrá'),
('Audi', 'A4', 2020, 19500, 'Výkonné vozidlo s progresívnym dizajnom. Asistent parkovania, hlasové ovládanie.', 'uploads/audi.jpg', 'Diesel', 'Diesel', 'Automatická', 163, 38000, 'Šedá');

EOF

echo "✅ Databáza bola úspešne vytvorená!"
