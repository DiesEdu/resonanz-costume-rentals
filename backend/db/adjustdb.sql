ALTER TABLE costumes MODIFY COLUMN category ENUM('TRMS', 'JCO', 'BMS', 'TRCC', 'ARMONIA') NOT NULL;
ALTER TABLE costumes DROP COLUMN price;
ALTER TABLE costumes ADD COLUMN container VARCHAR(255) NOT NULL AFTER category;
------------------------------------------------------------------------------------
ALTER TABLE bookings MODIFY COLUMN status ENUM('waiting_approval', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'waiting_approval';
ALTER TABLE bookings DROP COLUMN total_price;
ALTER TABLE users ADD COLUMN role ENUM('admin', 'costume_management', 'user') NOT NULL DEFAULT 'user';
DROP TABLE bookings;
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    costume_id INT NOT NULL,
    customer_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    size VARCHAR(10) NOT NULL,
    status ENUM('waiting_approval', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'waiting_approval',
    booking_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (costume_id) REFERENCES costumes(id) ON DELETE CASCADE
);
ALTER TABLE costumes ADD COLUMN costume_code VARCHAR(255) NOT NULL AFTER name;
----------------------------------------------------------------------------------------
ALTER TABLE costumes ADD COLUMN amount INT NOT NULL AFTER container;
ALTER TABLE bookings ADD COLUMN amount_book INT NOT NULL AFTER size;
----------------------------------------------------------------------------------------
DROP TABLE costume_sizes;
ALTER TABLE costumes DROP COLUMN container;
ALTER TABLE costumes ADD COLUMN rack_id INT NOT NULL AFTER category;
ALTER TABLE costumes ADD COLUMN size VARCHAR(255) NOT NULL AFTER rack_id;
ALTER TABLE costumes DROP COLUMN category;
ALTER TABLE costumes ADD COLUMN group_category VARCHAR(255) NOT NULL AFTER costume_code;
ALTER TABLE costumes
DROP COLUMN amount,
DROP COLUMN description,
DROP COLUMN available,
DROP COLUMN rating,
DROP COLUMN reviews;
CREATE TABLE IF NOT EXISTS group_category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
CREATE TABLE IF NOT EXISTS costume_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    costume_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (costume_id) REFERENCES costumes(id) ON DELETE CASCADE
)
----------------------------------------------------------------------------------------
ALTER TABLE bookings DROP COLUMN size;
----------------------------------------------------------------------------------------
-- Move size column from costumes to costume_stock table
ALTER TABLE costume_stock ADD COLUMN size VARCHAR(255) NOT NULL AFTER quantity;
-- Migrate existing size data from costumes to costume_stock
UPDATE costume_stock cs
JOIN costumes c ON cs.costume_id = c.id
SET cs.size = c.size
WHERE c.size IS NOT NULL 
  AND c.size != ''
  AND (cs.size IS NULL OR cs.size = '');
-- Remove size column from costumes table
ALTER TABLE costumes DROP COLUMN size;

ALTER TABLE bookings ADD COLUMN costume_stock_id INT NOT NULL AFTER costume_id;
ALTER TABLE bookings ADD
FOREIGN KEY (costume_stock_id) REFERENCES costume_stock(id) ON DELETE CASCADE;

ALTER TABLE bookings
DROP FOREIGN KEY bookings_ibfk_1;
ALTER TABLE bookings DROP COLUMN costume_id;

ALTER TABLE costumes ADD COLUMN description TEXT AFTER image;