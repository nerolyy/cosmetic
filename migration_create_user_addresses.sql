-- Создание таблицы для хранения адресов пользователей и любимых магазинов

CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL COMMENT 'ID пользователя',
    `shop_id` INT(11) NULL COMMENT 'ID любимого магазина (если выбран самовывоз)',
    `delivery_address` TEXT NULL COMMENT 'Адрес для доставки курьером',
    `is_default` TINYINT(1) DEFAULT 0 COMMENT 'Адрес по умолчанию',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`shop_id`) REFERENCES `shops`(`id`) ON DELETE SET NULL,
    KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Адреса пользователей';

