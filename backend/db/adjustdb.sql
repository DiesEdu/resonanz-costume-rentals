ALTER TABLE costumes MODIFY COLUMN category ENUM('TRMS', 'JCO', 'BMS', 'TRCC', 'ARMONIA') NOT NULL;
ALTER TABLE costumes DROP COLUMN price;
ALTER TABLE costumes ADD COLUMN container VARCHAR(255) NOT NULL AFTER category;
------------------------------------------------------------------------------------
ALTER TABLE bookings MODIFY COLUMN status ENUM('waiting_approval', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'waiting_approval';
ALTER TABLE bookings DROP COLUMN total_price;
ALTER TABLE users ADD COLUMN role ENUM('admin', 'costume_management', 'user') NOT NULL DEFAULT 'user';