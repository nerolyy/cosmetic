<?php
/**
 * Скрипт быстрого старта для установки базы данных
 * Автоматически создает все таблицы, применяет миграции и импортирует данные из data.sql (если файл существует)
 */

// Настройки подключения к БД (из config.php)
$db_host = 'localhost';
$db_name = 'cosmetic_shop';
$db_user = 'root';
$db_pass = 'root';
$db_port = 8889;

$errors = [];
$messages = [];
$step = $_GET['step'] ?? 'check';

// Функция для безопасного выполнения SQL
function executeSQL($pdo, $sql, $description = '') {
    try {
        if (is_array($sql)) {
            foreach ($sql as $query) {
                if (trim($query)) {
                    $pdo->exec($query);
                }
            }
        } else {
            if (trim($sql)) {
                $pdo->exec($sql);
            }
        }
        return ['success' => true, 'message' => $description ?: 'SQL выполнен успешно'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $description ?: 'Ошибка SQL', 'error' => $e->getMessage()];
    }
}

// Подключение к MySQL без выбора базы данных
try {
    $pdo_no_db = new PDO("mysql:host={$db_host};port={$db_port};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    $errors[] = 'Не удалось подключиться к MySQL: ' . $e->getMessage();
    $step = 'error';
}

if ($step === 'install' && empty($errors)) {
    try {
        // Создание базы данных
        $pdo_no_db->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $messages[] = "✓ База данных '{$db_name}' создана или уже существует";
        
        // Подключение к созданной базе данных
        $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // ========== СОЗДАНИЕ ТАБЛИЦ ==========
        
        // Таблица users
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'user',
                name VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы users");
        $messages[] = $result['success'] ? "✓ Таблица 'users' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица categories
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                parent_id INT DEFAULT NULL,
                is_hidden TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
                INDEX idx_hidden (is_hidden)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы categories");
        $messages[] = $result['success'] ? "✓ Таблица 'categories' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица brands
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS brands (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                logo VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы brands");
        $messages[] = $result['success'] ? "✓ Таблица 'brands' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица products
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                description TEXT,
                price DECIMAL(10, 2) NOT NULL,
                old_price DECIMAL(10, 2),
                discount INT DEFAULT 0,
                image VARCHAR(255),
                category_id INT,
                brand_id INT,
                stock INT DEFAULT 0,
                is_new TINYINT(1) DEFAULT 0,
                is_featured TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
                INDEX idx_category (category_id),
                INDEX idx_brand (brand_id),
                INDEX idx_featured (is_featured),
                INDEX idx_new (is_new)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы products");
        $messages[] = $result['success'] ? "✓ Таблица 'products' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица cart
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS cart (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
                UNIQUE KEY unique_cart_item (user_id, product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы cart");
        $messages[] = $result['success'] ? "✓ Таблица 'cart' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица favorites
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                UNIQUE KEY unique_favorite (user_id, product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы favorites");
        $messages[] = $result['success'] ? "✓ Таблица 'favorites' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица shops
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS shops (
                id INT(11) NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL COMMENT 'Название магазина',
                address VARCHAR(500) NOT NULL COMMENT 'Адрес магазина',
                latitude DECIMAL(10, 8) NOT NULL COMMENT 'Широта (координата Y)',
                longitude DECIMAL(11, 8) NOT NULL COMMENT 'Долгота (координата X)',
                description TEXT COMMENT 'Описание магазина',
                how_to_get TEXT COMMENT 'Как добраться до магазина',
                phone VARCHAR(20) COMMENT 'Телефон магазина',
                work_hours VARCHAR(100) COMMENT 'Режим работы',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_coordinates (latitude, longitude)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Магазины'
        ", "Создание таблицы shops");
        $messages[] = $result['success'] ? "✓ Таблица 'shops' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица orders
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                total DECIMAL(10, 2) NOT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                recipient_name VARCHAR(100),
                recipient_phone VARCHAR(20),
                delivery_method VARCHAR(20),
                address TEXT,
                shop_id INT NULL,
                delivery_date DATE NULL,
                promo_code_id INT DEFAULT NULL,
                promo_code_discount DECIMAL(10, 2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL,
                INDEX idx_user (user_id),
                INDEX idx_status (status),
                INDEX idx_promo_code (promo_code_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы orders");
        $messages[] = $result['success'] ? "✓ Таблица 'orders' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица order_items
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы order_items");
        $messages[] = $result['success'] ? "✓ Таблица 'order_items' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица promo_codes
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS promo_codes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(50) UNIQUE NOT NULL,
                description TEXT,
                discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
                discount_value DECIMAL(10, 2) NOT NULL,
                min_order_amount DECIMAL(10, 2) DEFAULT 0,
                max_order_amount DECIMAL(10, 2) DEFAULT NULL,
                max_discount DECIMAL(10, 2) DEFAULT NULL,
                max_uses INT DEFAULT NULL,
                current_uses INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                valid_from DATETIME DEFAULT CURRENT_TIMESTAMP,
                valid_until DATETIME DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_code (code),
                INDEX idx_active (is_active),
                INDEX idx_valid_dates (valid_from, valid_until)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы promo_codes");
        $messages[] = $result['success'] ? "✓ Таблица 'promo_codes' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица promo_code_uses
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS promo_code_uses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                promo_code_id INT NOT NULL,
                user_id INT NOT NULL,
                order_id INT DEFAULT NULL,
                used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
                INDEX idx_promo_code (promo_code_id),
                INDEX idx_user (user_id),
                INDEX idx_order (order_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы promo_code_uses");
        $messages[] = $result['success'] ? "✓ Таблица 'promo_code_uses' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица user_addresses
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS user_addresses (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL COMMENT 'ID пользователя',
                shop_id INT(11) NULL COMMENT 'ID любимого магазина (если выбран самовывоз)',
                delivery_address TEXT NULL COMMENT 'Адрес для доставки курьером',
                is_default TINYINT(1) DEFAULT 0 COMMENT 'Адрес по умолчанию',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL,
                KEY idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Адреса пользователей'
        ", "Создание таблицы user_addresses");
        $messages[] = $result['success'] ? "✓ Таблица 'user_addresses' создана" : "✗ Ошибка: " . $result['error'];
        
        // Таблица brand_favorites
        $result = executeSQL($pdo, "
            CREATE TABLE IF NOT EXISTS brand_favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                brand_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
                UNIQUE KEY unique_brand_favorite (user_id, brand_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ", "Создание таблицы brand_favorites");
        $messages[] = $result['success'] ? "✓ Таблица 'brand_favorites' создана" : "✗ Ошибка: " . $result['error'];
        
        // Добавление внешнего ключа promo_code_id в orders (если еще не существует)
        $result = executeSQL($pdo, "
            SET @dbname = DATABASE();
            SET @tablename = 'orders';
            SET @preparedStatement = (SELECT IF(
                (
                    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE
                        (TABLE_SCHEMA = @dbname)
                        AND (TABLE_NAME = @tablename)
                        AND (COLUMN_NAME = 'promo_code_id')
                        AND (REFERENCED_TABLE_NAME = 'promo_codes')
                ) > 0,
                'SELECT 1',
                CONCAT('ALTER TABLE ', @tablename, ' ADD FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id) ON DELETE SET NULL')
            ));
            PREPARE alterIfNotExists FROM @preparedStatement;
            EXECUTE alterIfNotExists;
            DEALLOCATE PREPARE alterIfNotExists;
        ", "Добавление внешнего ключа promo_code_id");
        $messages[] = $result['success'] ? "✓ Внешний ключ promo_code_id добавлен" : "ℹ " . ($result['error'] ?? 'Уже существует');
        
        // ========== ИМПОРТ ДАННЫХ ИЗ ФАЙЛА (если существует) ==========
        
        $dataFile = __DIR__ . '/data.sql';
        if (file_exists($dataFile)) {
            $messages[] = "ℹ Найден файл data.sql, начинаю импорт данных...";
            
            try {
                $sqlData = file_get_contents($dataFile);
                if ($sqlData) {
                    // Разбиваем SQL на отдельные запросы
                    $queries = array_filter(
                        array_map('trim', explode(';', $sqlData)),
                        function($query) {
                            return !empty($query) && !preg_match('/^--/', $query) && !preg_match('/^\/\*/', $query);
                        }
                    );
                    
                    $imported = 0;
                    foreach ($queries as $query) {
                        if (trim($query)) {
                            try {
                                $pdo->exec($query);
                                $imported++;
                            } catch (PDOException $e) {
                                // Игнорируем ошибки дублирования (INSERT IGNORE, UNIQUE и т.д.)
                                if (strpos($e->getMessage(), 'Duplicate') === false && 
                                    strpos($e->getMessage(), 'already exists') === false) {
                                    $messages[] = "⚠ Предупреждение при импорте: " . substr($e->getMessage(), 0, 100);
                                }
                            }
                        }
                    }
                    
                    if ($imported > 0) {
                        $messages[] = "✓ Данные из data.sql успешно импортированы ({$imported} запросов)";
                    } else {
                        $messages[] = "ℹ Файл data.sql пуст или содержит только комментарии";
                    }
                }
            } catch (Exception $e) {
                $messages[] = "⚠ Ошибка при импорте data.sql: " . $e->getMessage();
            }
        } else {
            $messages[] = "ℹ Файл data.sql не найден. Для импорта данных создайте файл data.sql в корне проекта.";
        }
        
        $step = 'success';
        
    } catch (PDOException $e) {
        $errors[] = 'Ошибка установки: ' . $e->getMessage();
        $step = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка базы данных - Косметика</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #FCE4EC 0%, #F8BBD0 50%, #FCE4EC 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .install-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(236, 64, 122, 0.2);
            padding: 40px;
            max-width: 700px;
            width: 100%;
        }
        
        h1 {
            color: #EC407A;
            font-size: 32px;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .subtitle {
            color: #757575;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .info-box {
            background: linear-gradient(135deg, #FCE4EC, #F8BBD0);
            border: 2px solid #F48FB1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-box h2 {
            color: #EC407A;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .info-box p {
            color: #424242;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .info-box code {
            background: rgba(255, 255, 255, 0.7);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #C62828;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #EC407A, #F06292);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #F06292, #EC407A);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(236, 64, 122, 0.4);
        }
        
        .messages {
            margin-top: 20px;
        }
        
        .message {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            background: #E8F5E9;
            color: #2E7D32;
            border-left: 4px solid #4CAF50;
        }
        
        .message.error {
            background: #FFEBEE;
            color: #C62828;
            border-left-color: #EF5350;
        }
        
        .message.info {
            background: #E3F2FD;
            color: #1976D2;
            border-left-color: #2196F3;
        }
        
        .success-box {
            background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
            border: 2px solid #66BB6A;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
        }
        
        .success-box h2 {
            color: #2E7D32;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .success-box p {
            color: #424242;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .credentials {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            border: 2px solid #66BB6A;
        }
        
        .credentials h3 {
            color: #2E7D32;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .credentials p {
            margin: 5px 0;
            font-family: 'Courier New', monospace;
            color: #424242;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1>🚀 Быстрый старт</h1>
        <p class="subtitle">Автоматическая установка базы данных</p>
        
        <?php if ($step === 'check' || $step === 'error'): ?>
            <div class="info-box">
                <h2>📋 Информация об установке</h2>
                <p><strong>Этот скрипт выполнит:</strong></p>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>Создание базы данных <code><?= htmlspecialchars($db_name) ?></code></li>
                    <li>Создание всех необходимых таблиц</li>
                    <li>Применение всех миграций</li>
                </ul>
                <p style="margin-top: 15px;"><strong>Параметры подключения:</strong></p>
                <p>Хост: <code><?= htmlspecialchars($db_host) ?></code>, Порт: <code><?= htmlspecialchars($db_port) ?></code></p>
                <p>Пользователь: <code><?= htmlspecialchars($db_user) ?></code></p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="messages">
                    <?php foreach ($errors as $error): ?>
                        <div class="message error">✗ <?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <a href="?step=install" class="btn">Начать установку</a>
            
        <?php elseif ($step === 'install'): ?>
            <div class="messages">
                <?php foreach ($messages as $msg): ?>
                    <div class="message"><?= htmlspecialchars($msg) ?></div>
                <?php endforeach; ?>
            </div>
            
        <?php elseif ($step === 'success'): ?>
            <div class="success-box">
                <h2>✅ Установка завершена успешно!</h2>
                <p>База данных создана со всеми необходимыми таблицами.</p>
                
                <p style="margin-top: 20px;"><strong>⚠️ Важно:</strong> После установки удалите или переименуйте файл <code>install.php</code> в целях безопасности!</p>
                
                <a href="index.php" class="btn" style="margin-top: 20px;">Перейти на главную страницу</a>
                <a href="admin/index.php" class="btn" style="margin-top: 10px; background: linear-gradient(135deg, #2196F3, #1976D2);">Открыть админ-панель</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

