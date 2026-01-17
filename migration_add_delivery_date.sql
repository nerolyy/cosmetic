-- Добавление поля delivery_date в таблицу orders для хранения даты доставки
-- Если колонка уже существует, будет показано сообщение

-- Проверка и добавление delivery_date
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'orders' 
    AND COLUMN_NAME = 'delivery_date');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN delivery_date DATE NULL AFTER shop_id', 
    'SELECT "Column delivery_date already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

