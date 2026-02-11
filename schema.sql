-- ============================================================
-- Klinik Dr. Azhar - Inventory Management System
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS klinik_azhar_db;
USE klinik_azhar_db;

-- ------------------------------------------------------------
-- Table: categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Table: suppliers
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS suppliers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    contact_name VARCHAR(100),
    phone        VARCHAR(20),
    email        VARCHAR(100),
    address      TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Table: medicines (main inventory)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medicines (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    generic_name    VARCHAR(150),
    category_id     INT,
    supplier_id     INT,
    unit            VARCHAR(30) NOT NULL DEFAULT 'pcs',  -- e.g. tablet, bottle, box
    stock_quantity  INT NOT NULL DEFAULT 0,
    reorder_level   INT NOT NULL DEFAULT 10,             -- alert if stock <= this
    unit_price      DECIMAL(10,2) DEFAULT 0.00,
    expiry_date     DATE,
    location        VARCHAR(100),                        -- shelf/cabinet location
    description     TEXT,
    status          ENUM('active','inactive') DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Table: stock_transactions (in/out history)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS stock_transactions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id     INT NOT NULL,
    type            ENUM('in','out','adjustment') NOT NULL,
    quantity        INT NOT NULL,
    notes           TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Table: users (admin login)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(50) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    full_name    VARCHAR(100),
    role         ENUM('admin','staff') DEFAULT 'staff',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Seed: default admin user (password: admin123)
-- ------------------------------------------------------------
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- ------------------------------------------------------------
-- Seed: sample categories
-- ------------------------------------------------------------
INSERT INTO categories (name, description) VALUES
('Antibiotics', 'Medicines used to treat bacterial infections'),
('Analgesics', 'Pain relief medications'),
('Vitamins & Supplements', 'Nutritional supplements'),
('Antihistamines', 'Allergy relief medications'),
('Antacids', 'Digestive and stomach relief medications');

-- ------------------------------------------------------------
-- Seed: sample supplier
-- ------------------------------------------------------------
INSERT INTO suppliers (name, contact_name, phone, email) VALUES
('MedSupply Sdn Bhd', 'Ahmad Razif', '03-12345678', 'order@medsupply.com.my'),
('PharmaCare Malaysia', 'Lim Wei Xin', '03-87654321', 'sales@pharmacare.com.my');

-- ------------------------------------------------------------
-- Seed: sample medicines
-- ------------------------------------------------------------
INSERT INTO medicines (name, generic_name, category_id, supplier_id, unit, stock_quantity, reorder_level, unit_price, expiry_date, location) VALUES
('Amoxicillin 500mg', 'Amoxicillin', 1, 1, 'capsule', 200, 50, 0.80, '2026-06-30', 'Shelf A1'),
('Paracetamol 500mg', 'Paracetamol', 2, 1, 'tablet', 500, 100, 0.10, '2027-01-15', 'Shelf A2'),
('Vitamin C 1000mg', 'Ascorbic Acid', 3, 2, 'tablet', 8, 20, 0.50, '2025-12-31', 'Shelf B1'),
('Cetirizine 10mg', 'Cetirizine', 4, 2, 'tablet', 150, 30, 0.30, '2026-09-01', 'Shelf B2'),
('Omeprazole 20mg', 'Omeprazole', 5, 1, 'capsule', 3, 25, 1.20, '2026-03-20', 'Shelf C1');
