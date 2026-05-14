<?php
// ГЛАВНЫЙ КОНФИГ ФАЙЛ
// Подключается на всех страницах

// Включаем отображение ошибок (удобно для разработки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Определяем корневую директорию проекта
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Базовый URL сайта (подстройте под свой путь, если нужно)
// Например: 'http://localhost:8888/cosmetic/'
define('BASE_URL', 'http://localhost:8888/cosmetic/');

/**
 * Единый path для cookie сессии: иначе PHP ставит path по каталогу скрипта
 * (/cosmetic/api/ для API и /cosmetic/ для страниц) — браузер не шлёт cookie на другие пути,
 * корзина «пропадает» после F5 или после запросов к API.
 */
function cosmetic_session_cookie_path(): string
{
    $parts = parse_url(BASE_URL);
    $path = isset($parts['path']) ? (string) $parts['path'] : '/';
    $path = '/' . trim($path, '/');
    if ($path !== '/') {
        $path .= '/';
    }
    return $path;
}

$__cosmetic_cookie_path = cosmetic_session_cookie_path();
$__cosmetic_base_parts = parse_url(BASE_URL);
$__cosmetic_secure = !empty($__cosmetic_base_parts['scheme']) && $__cosmetic_base_parts['scheme'] === 'https';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $__cosmetic_cookie_path,
        'domain' => '',
        'secure' => $__cosmetic_secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Пути к папкам
define('ASSETS_PATH', ROOT_PATH . 'assets' . DIRECTORY_SEPARATOR);
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOADS_URL', BASE_URL . 'uploads/');

// ==========================
// SMTP (отправка писем)
// ==========================
// Заполните эти настройки, чтобы работало подтверждение регистрации по email.
// Пример (для Gmail нужны App Password и корректные настройки аккаунта):
// SMTP_HOST=smtp.gmail.com, SMTP_PORT=587, SMTP_ENCRYPTION=tls
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // tls | ssl | none
define('SMTP_USERNAME', 'jonsonsins111@gmail.com');
define('SMTP_PASSWORD', 'ffjlmzczxltiwluz');
define('SMTP_FROM_EMAIL', 'jonsonsins111@gmail.com');
define('SMTP_FROM_NAME', 'Cosmetic');
// Если SMTP-сертификат не проходит проверку (часто на локальной разработке),
// можно временно отключить верификацию. На проде лучше оставить true.
define('SMTP_VERIFY_PEER', true);
define('SMTP_ALLOW_SELF_SIGNED', false);

// Google reCAPTCHA v2 («Я не робот»): https://www.google.com/recaptcha/admin
// Создайте ключи для типа reCAPTCHA v2 → «Я не робот» и укажите домен сайта (для localhost добавьте localhost).
define('RECAPTCHA_SITE_KEY', '6Ld-BeosAAAAAFmaYdsZY9qYrc0ni3OUkGdYI3NF');
define('RECAPTCHA_SECRET_KEY', '6Ld-BeosAAAAAE2OzR98eaHNpIbSB0rlKM7D6V7n');

// Обратная связь: входящие письма (если пусто — на SMTP_FROM_EMAIL)
define('FEEDBACK_INBOX_EMAIL', '');

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
 * Убираем ON DELETE CASCADE у cart.product_id: иначе при удалении/пересоздании товара
 * строки корзины исчезают из БД и после F5 корзина пустая.
 */
function cartEnsureProductFkNoCascadeOnDelete(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    global $pdo;

    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'cart'");
        if ($stmt->rowCount() === 0) {
            return;
        }

        $fkStmt = $pdo->query("
            SELECT kcu.CONSTRAINT_NAME, rc.DELETE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
              ON rc.CONSTRAINT_SCHEMA = kcu.TABLE_SCHEMA
             AND rc.TABLE_NAME = kcu.TABLE_NAME
             AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
            WHERE kcu.TABLE_SCHEMA = DATABASE()
              AND kcu.TABLE_NAME = 'cart'
              AND kcu.COLUMN_NAME = 'product_id'
              AND kcu.REFERENCED_TABLE_NAME = 'products'
            LIMIT 1
        ");
        $row = $fkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }

        $rule = strtoupper((string) $row['DELETE_RULE']);
        if ($rule === 'RESTRICT' || $rule === 'NO ACTION') {
            return;
        }

        $fkName = (string) $row['CONSTRAINT_NAME'];
        $safeFk = str_replace('`', '``', $fkName);
        $pdo->exec("ALTER TABLE cart DROP FOREIGN KEY `{$safeFk}`");
        $pdo->exec('ALTER TABLE cart ADD CONSTRAINT cart_product_id_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE');
    } catch (Throwable $e) {
        error_log('cartEnsureProductFkNoCascadeOnDelete: ' . $e->getMessage());
    }
}

cartEnsureProductFkNoCascadeOnDelete();

/**
 * Получить текущего пользователя
 */
function getCurrentUser(): ?array
{
    // Если в сессии нет ID пользователя — он не авторизован
    if (empty($_SESSION['user_id'])) {
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
 * Проверка авторизации пользователя
 * Дополнительно убеждаемся, что пользователь действительно существует в БД.
 */
function isLoggedIn(): bool
{
    return getCurrentUser() !== null;
}

function recaptcha_is_configured(): bool
{
    $site = trim((string)(defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : ''));
    $secret = trim((string)(defined('RECAPTCHA_SECRET_KEY') ? RECAPTCHA_SECRET_KEY : ''));

    return $site !== '' && $secret !== '';
}

/**
 * Запрос к Google siteverify (file_get_contents, при неудаче — cURL).
 */
function recaptcha_siteverify_request(string $postBody): ?string
{
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $postBody,
            'timeout' => 12,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw !== false && $raw !== '') {
        return $raw;
    }

    if (!function_exists('curl_init')) {
        return null;
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return null;
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postBody,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $out = curl_exec($ch);
    curl_close($ch);

    if ($out === false || $out === '') {
        return null;
    }

    return $out;
}

/**
 * Краткая подсказка после неудачной проверки (для текста ошибки на форме).
 */
function recaptcha_failure_hint_for_user(): string
{
    $e = $_SESSION['recaptcha_last_error'] ?? null;
    if ($e === 'network') {
        return ' Сервер не смог связаться с Google (интернет, файрвол, антивирус или блокировка HTTPS). Попробуйте с другой сети или попросите администратора проверить PHP (openssl, cURL).';
    }
    if ($e === 'missing_token') {
        return ' Отправьте форму ещё раз и сразу после галочки нажмите кнопку (токен капчи действует недолго).';
    }
    if (is_array($e) && !empty($e['google']) && is_array($e['google'])) {
        $codes = $e['google'];
        if (in_array('invalid-input-secret', $codes, true)) {
            return ' В config.php указан неверный RECAPTCHA_SECRET_KEY.';
        }
        if (in_array('invalid-input-response', $codes, true)) {
            return ' Токен капчи отклонён — пройдите капчу заново и отправьте форму.';
        }
        if (in_array('timeout-or-duplicate', $codes, true)) {
            return ' Капча устарела — снимите галочку и пройдите проверку снова, затем сразу отправьте форму.';
        }
        if (in_array('hostname-mismatch', $codes, true) || in_array('bad-request', $codes, true)) {
            return ' Домен сайта не добавлен в настройках ключа reCAPTCHA (Google Admin) или открываете сайт с другого адреса (localhost / IP / другое имя).';
        }
    }

    return '';
}

/**
 * Проверка ответа reCAPTCHA v2 (поле POST g-recaptcha-response).
 */
function recaptcha_verify_post(): bool
{
    unset($_SESSION['recaptcha_last_error']);

    if (!recaptcha_is_configured()) {
        $_SESSION['recaptcha_last_error'] = 'not_configured';
        return false;
    }

    $token = trim((string) ($_POST['g-recaptcha-response'] ?? ''));
    if ($token === '') {
        $_SESSION['recaptcha_last_error'] = 'missing_token';
        return false;
    }

    $post = http_build_query([
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $raw = recaptcha_siteverify_request($post);
    if ($raw === null || $raw === '') {
        $_SESSION['recaptcha_last_error'] = 'network';
        return false;
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['success'])) {
        $_SESSION['recaptcha_last_error'] = [
            'google' => isset($data['error-codes']) && is_array($data['error-codes']) ? $data['error-codes'] : [],
        ];
        return false;
    }

    return true;
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

/**
 * ==========================
 * КОЛЕСО ФОРТУНЫ (бонусы)
 * ==========================
 */

function wheelEnsureTables(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    global $pdo;

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_wheel_spins (
            user_id INT NOT NULL PRIMARY KEY,
            spins INT NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_user_wheel_spins_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wheel_spin_history (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            spin_delta INT NOT NULL,
            reason VARCHAR(64) NOT NULL,
            order_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_wheel_history_user (user_id),
            CONSTRAINT fk_wheel_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wheel_rewards (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            reward_type VARCHAR(32) NOT NULL,
            target_id INT NOT NULL,
            target_name VARCHAR(255) NOT NULL,
            discount_percent INT NOT NULL,
            promo_code VARCHAR(32) NOT NULL UNIQUE,
            is_used TINYINT(1) NOT NULL DEFAULT 0,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_wheel_rewards_user (user_id),
            CONSTRAINT fk_wheel_rewards_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $initialized = true;
}

function feedbackEnsureTable(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    global $pdo;
    $pdo->exec('CREATE TABLE IF NOT EXISTS contact_feedback (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body MEDIUMTEXT NOT NULL,
        ip VARCHAR(45) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_contact_feedback_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $done = true;
}

function wheelGetSpins(int $userId): int
{
    wheelEnsureTables();
    global $pdo;

    $stmt = $pdo->prepare("SELECT spins FROM user_wheel_spins WHERE user_id = ?");
    $stmt->execute([$userId]);
    $spins = $stmt->fetchColumn();

    if ($spins === false) {
        $stmtIns = $pdo->prepare("INSERT INTO user_wheel_spins (user_id, spins) VALUES (?, 0)");
        $stmtIns->execute([$userId]);
        return 0;
    }

    return (int)$spins;
}

function wheelAddSpins(int $userId, int $amount, string $reason, ?int $orderId = null): void
{
    if ($amount === 0) {
        return;
    }
    wheelEnsureTables();
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO user_wheel_spins (user_id, spins)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE spins = GREATEST(0, spins + VALUES(spins))
    ");
    $stmt->execute([$userId, $amount]);

    $stmtLog = $pdo->prepare("
        INSERT INTO wheel_spin_history (user_id, spin_delta, reason, order_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmtLog->execute([$userId, $amount, $reason, $orderId]);
}





