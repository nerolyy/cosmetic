-- Добавление поля shop_id в таблицу orders для самовывоза
-- Если колонка уже существует, будет ошибка - просто пропустите ее

-- Проверка и добавление shop_id
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'orders' 
    AND COLUMN_NAME = 'shop_id');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN shop_id INT NULL AFTER address, ADD FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL', 
    'SELECT "Column shop_id already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

