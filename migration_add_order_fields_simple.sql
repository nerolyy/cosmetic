-- Упрощенная миграция: добавление недостающих полей для получателя и доставки
-- Если колонка уже существует, будет ошибка - просто пропустите ее

-- Добавляем recipient_name (если еще нет)
ALTER TABLE orders ADD COLUMN recipient_name VARCHAR(100) AFTER status;

-- Добавляем recipient_phone (если еще нет)
ALTER TABLE orders ADD COLUMN recipient_phone VARCHAR(20) AFTER recipient_name;

-- Добавляем delivery_method (если еще нет)
ALTER TABLE orders ADD COLUMN delivery_method VARCHAR(20) AFTER recipient_phone;

-- Добавляем address (если еще нет) - эта колонка может уже существовать
-- Если получите ошибку "Duplicate column name 'address'", значит она уже есть - это нормально
ALTER TABLE orders ADD COLUMN address TEXT AFTER delivery_method;

