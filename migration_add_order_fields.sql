-- Безопасное добавление полей для получателя и доставки в таблицу orders
-- Проверяем и добавляем только те колонки, которых еще нет

-- Проверка и добавление recipient_name
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'orders' 
    AND COLUMN_NAME = 'recipient_name');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN recipient_name VARCHAR(100) AFTER status', 
    'SELECT "Column recipient_name already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Проверка и добавление recipient_phone
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'orders' 
    AND COLUMN_NAME = 'recipient_phone');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN recipient_phone VARCHAR(20) AFTER recipient_name', 
    'SELECT "Column recipient_phone already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Проверка и добавление delivery_method
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'orders' 
    AND COLUMN_NAME = 'delivery_method');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN delivery_method VARCHAR(20) AFTER recipient_phone', 
    'SELECT "Column delivery_method already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Проверка и добавление address (если еще нет)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'orders' 
    AND COLUMN_NAME = 'address');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN address TEXT AFTER delivery_method', 
    'SELECT "Column address already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

