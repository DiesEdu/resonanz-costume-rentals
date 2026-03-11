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
    category ENUM('TRMS', 'JCO', 'BMS', 'TRCC', 'ARMONIA') NOT NULL,
    container VARCHAR(255) NOT NULL,
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
    costume_name VARCHAR(255) NOT NULL,
    costume_image VARCHAR(500),
    customer_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    size VARCHAR(10) NOT NULL,
    status ENUM('waiting_approval', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'waiting_approval',
    booking_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (costume_id) REFERENCES costumes(id) ON DELETE CASCADE
);

-- ========================
-- Seed Data
-- ========================

INSERT INTO costumes (id, name, category, price, description, image, available, rating, reviews) VALUES
(1,  'Elegant Victorian Ball Gown',   'Historical', 85.00, 'Stunning 19th-century inspired ball gown with intricate lace details and corset bodice. Perfect for themed parties and historical reenactments.', 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=500&auto=format&fit=crop&q=60', 1, 4.8, 24),
(2,  'Superhero Spider Suit',          'Superhero',  65.00, 'High-quality Spider-Man inspired suit with muscle padding and web patterns. Includes mask and wrist web-shooters.',                              'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=500&auto=format&fit=crop&q=60', 1, 4.9, 56),
(3,  'Renaissance Pirate Captain',     'Pirate',     75.00, 'Authentic pirate captain outfit with tricorn hat, leather boots, and detailed coat. Includes prop sword and belt accessories.',               'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500&auto=format&fit=crop&q=60', 1, 4.7, 18),
(4,  'Princess Elsa Ice Dress',        'Disney',     55.00, 'Magical ice queen dress with shimmering fabric and flowing cape. Perfect for children''s parties and cosplay events.',                        'https://images.unsplash.com/photo-1534445967719-8ae7b972b1a5?w=500&auto=format&fit=crop&q=60', 1, 4.9, 89),
(5,  '1920s Gatsby Flapper Dress',     'Vintage',    70.00, 'Art Deco inspired flapper dress with beaded fringe and headband. Perfect for Great Gatsby themed parties.',                                   'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=500&auto=format&fit=crop&q=60', 1, 4.6, 32),
(6,  'Medieval Knight Armor',          'Historical', 95.00, 'Full metal-effect knight armor with chainmail details. Includes helmet, gauntlets, and sword. Great for medieval fairs.',                     'https://images.unsplash.com/photo-1598556883438-2c96b1b5b5b5?w=500&auto=format&fit=crop&q=60', 1, 4.8, 15),
(7,  'Wonder Woman Costume',           'Superhero',  70.00, 'Iconic Amazon warrior costume with tiara, arm bands, and lasso accessory. High-quality materials for comfort.',                               'https://images.unsplash.com/photo-1569003339405-ea396a5a8a90?w=500&auto=format&fit=crop&q=60', 1, 4.7, 43),
(8,  'Harry Potter Wizard Robes',      'Fantasy',    45.00, 'Authentic Hogwarts house robes with house crest. Choose your house! Includes wand and scarf.',                                               'https://images.unsplash.com/photo-1547592166-23acbe346499?w=500&auto=format&fit=crop&q=60', 1, 4.9, 67),
(9,  'Vampire Count Dracula',          'Horror',     60.00, 'Classic vampire costume with velvet cape, vest, and fangs. Perfect for Halloween and gothic events.',                                        'https://images.unsplash.com/photo-1509557965875-b88c97052f0e?w=500&auto=format&fit=crop&q=60', 1, 4.5, 28),
(10, 'Astronaut Space Suit',           'Career',     80.00, 'NASA-inspired astronaut suit with patches, helmet, and backpack. Great for space-themed parties.',                                           'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=500&auto=format&fit=crop&q=60', 1, 4.8, 21),
(11, 'Geisha Traditional Kimono',      'Cultural',   90.00, 'Authentic Japanese kimono with obi belt, traditional footwear, and hair accessories. Beautiful silk fabric.',                                'https://images.unsplash.com/photo-1492571350019-22de08371fd3?w=500&auto=format&fit=crop&q=60', 1, 4.9, 34),
(12, 'Zombie Apocalypse Survivor',     'Horror',     50.00, 'Post-apocalyptic survivor outfit with distressed clothing, fake blood, and survival gear props.',                                            'https://images.unsplash.com/photo-1509248961158-e54f6934749c?w=500&auto=format&fit=crop&q=60', 1, 4.4, 19);

INSERT INTO costume_sizes (costume_id, size) VALUES
(1, 'S'), (1, 'M'), (1, 'L'),
(2, 'S'), (2, 'M'), (2, 'L'), (2, 'XL'),
(3, 'M'), (3, 'L'), (3, 'XL'),
(4, 'XS'), (4, 'S'), (4, 'M'),
(5, 'XS'), (5, 'S'), (5, 'M'), (5, 'L'),
(6, 'M'), (6, 'L'), (6, 'XL'),
(7, 'XS'), (7, 'S'), (7, 'M'), (7, 'L'),
(8, 'S'), (8, 'M'), (8, 'L'),
(9, 'M'), (9, 'L'), (9, 'XL'),
(10, 'S'), (10, 'M'), (10, 'L'),
(11, 'XS'), (11, 'S'), (11, 'M'),
(12, 'S'), (12, 'M'), (12, 'L'), (12, 'XL');

-- Sample booking
INSERT INTO bookings (costume_id, costume_name, costume_image, customer_name, email, phone, start_date, end_date, size, status, booking_date) VALUES
(2, 'Superhero Spider Suit', 'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=500&auto=format&fit=crop&q=60', 'John Doe', 'john@example.com', '555-0123', '2026-03-15', '2026-03-18', 'M', 'processing', '2026-03-10');
