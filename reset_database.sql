-- Скрипт для полной переустановки базы данных cosmetic_shop
-- ВНИМАНИЕ: Этот скрипт удалит ВСЕ данные из базы данных!

-- Отключаем проверку внешних ключей
SET FOREIGN_KEY_CHECKS = 0;

-- Удаляем все таблицы
DROP TABLE IF EXISTS promo_code_uses;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS brand_favorites;
DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS promo_codes;
DROP TABLE IF EXISTS shops;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS users;

-- Включаем проверку внешних ключей обратно
SET FOREIGN_KEY_CHECKS = 1;

-- Теперь можно запустить install.php или импортировать data.sql




