<?php
// ГЛАВНЫЙ КОНФИГ ФАЙЛ
// Подключается на всех страницах

// Включаем отображение ошибок (удобно для разработки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Определяем корневую директорию проекта
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Базовый URL сайта (подстройте под свой путь, если нужно)
// Например: 'http://localhost:8888/cosmetic/'
define('BASE_URL', 'http://localhost:8888/cosmetic/');

// Пути к папкам
define('ASSETS_PATH', ROOT_PATH . 'assets' . DIRECTORY_SEPARATOR);
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOADS_URL', BASE_URL . 'uploads/');

// Настройки подключения к БД (MAMP по умолчанию: пользователь root / пароль root, порт 8889)
$db_host = 'localhost';
$db_name = 'cosmetic_shop';
$db_user = 'root';
$db_pass = 'root';
$db_port = 8889; // при необходимости измените под свою установку MySQL

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Если БД недоступна, выводим понятное сообщение
    die('Ошибка подключения к базе данных: ' . htmlspecialchars($e->getMessage()));
}

// ==========================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ==========================

/**
 * Проверка авторизации пользователя
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Получить текущего пользователя
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    // Используем сессию для хранения данных, чтобы можно было их обновлять
    $cacheKey = 'current_user_data_' . $_SESSION['user_id'];
    $updateTime = $_SESSION['user_data_updated'] ?? 0;
    $lastLoadTime = $_SESSION['last_user_load'] ?? 0;
    
    // Если данные были обновлены после последней загрузки, очищаем кеш
    if (isset($_SESSION[$cacheKey]) && $updateTime > 0 && $updateTime > $lastLoadTime) {
        unset($_SESSION[$cacheKey]);
    }
    
    // Если данные есть в сессии и не устарели, возвращаем их
    if (isset($_SESSION[$cacheKey]) && is_array($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }

    global $pdo;

    $stmt = $pdo->prepare('SELECT id, email, name, phone, role, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        // если пользователь не найден в БД, очищаем сессию
        unset($_SESSION['user_id']);
        return null;
    }

    // Сохраняем в сессию
    $_SESSION[$cacheKey] = $user;
    $_SESSION['last_user_load'] = time();
    
    return $user;
}

/**
 * Очистить кеш текущего пользователя
 */
function clearUserCache(): void
{
    if (isset($_SESSION['user_id'])) {
        $cacheKey = 'current_user_data_' . $_SESSION['user_id'];
        unset($_SESSION[$cacheKey]);
        $_SESSION['user_data_updated'] = time();
    }
}

/**
 * Проверка, является ли пользователь администратором
 */
function isAdmin(): bool
{
    $user = getCurrentUser();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}





