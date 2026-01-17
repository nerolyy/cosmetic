-- Добавление поля is_hidden в таблицу categories
USE cosmetic_shop;

-- Проверяем, существует ли поле is_hidden, если нет - добавляем
SET @dbname = DATABASE();
SET @tablename = 'categories';
SET @columnname = 'is_hidden';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TINYINT(1) DEFAULT 0 AFTER parent_id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Добавляем индекс, если его нет
SET @indexname = 'idx_hidden';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX ', @indexname, ' ON ', @tablename, '(is_hidden)')
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

