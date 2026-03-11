-- Costume Rental Database Schema
-- Run this script to set up the MySQL database

-- Users table (authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50),
    role ENUM('admin', 'costume_management', 'user') NOT NULL DEFAULT 'user',
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE DATABASE IF NOT EXISTS costume_rental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE costume_rental;

-- Costumes table
CREATE TABLE IF NOT EXISTS costumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    costume_code VARCHAR(255) NOT NULL,
    category ENUM('TRMS', 'JCO', 'BMS', 'TRCC', 'ARMONIA') NOT NULL,
    container VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    description TEXT,
    image VARCHAR(500),
    available TINYINT(1) NOT NULL DEFAULT 1,
    rating DECIMAL(3, 1) DEFAULT 0.0,
    reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Costume sizes (many-to-many: each costume has multiple sizes)
CREATE TABLE IF NOT EXISTS costume_sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    costume_id INT NOT NULL,
    size VARCHAR(10) NOT NULL,
    FOREIGN KEY (costume_id) REFERENCES costumes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_costume_size (costume_id, size)
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    costume_id INT NOT NULL,
    costumer_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    size VARCHAR(10) NOT NULL,
    status ENUM('waiting_approval', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'waiting_approval',
    booking_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (costume_id) REFERENCES costumes(id) ON DELETE CASCADE
);