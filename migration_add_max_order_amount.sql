-- Добавление поля max_order_amount в таблицу promo_codes
-- Если колонка уже существует, скрипт не вызовет ошибку

SET @dbname = DATABASE();
SET @tablename = 'promo_codes';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = 'max_order_amount')
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN max_order_amount DECIMAL(10, 2) DEFAULT NULL AFTER min_order_amount')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

