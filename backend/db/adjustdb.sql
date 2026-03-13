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