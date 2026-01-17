-- Добавление роли в таблицу users

USE cosmetic_shop;

ALTER TABLE users
ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'user' AFTER password;

UPDATE users SET role = 'user' WHERE role IS NULL;

CREATE INDEX IF NOT EXISTS idx_role ON users(role);



