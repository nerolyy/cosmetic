<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

// Получаем данные из запроса
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

// Если JSON не распарсился, пробуем получить из POST
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
    $input = $_POST;
}

$promo_code = isset($input['code']) ? trim(strtoupper($input['code'])) : '';
$order_total = isset($input['order_total']) ? floatval($input['order_total']) : 0;

if (empty($promo_code)) {
    echo json_encode(['success' => false, 'message' => 'Введите промокод']);
    exit;
}

if ($order_total <= 0) {
    echo json_encode(['success' => false, 'message' => 'Сумма заказа должна быть больше нуля']);
    exit;
}

try {
    // Колесо фортуны: промокоды вида WHEELxxxxxx
    if (strpos($promo_code, 'WHEEL') === 0) {
        wheelEnsureTables();

        $stmtReward = $pdo->prepare("
            SELECT id, user_id, reward_type, target_id, target_name, discount_percent, promo_code, is_used, expires_at
            FROM wheel_rewards
            WHERE promo_code = ? AND user_id = ?
            LIMIT 1
        ");
        $stmtReward->execute([$promo_code, (int)$_SESSION['user_id']]);
        $reward = $stmtReward->fetch(PDO::FETCH_ASSOC);

        if (!$reward) {
            echo json_encode(['success' => false, 'message' => 'Промокод не найден или не принадлежит вашему аккаунту']);
            exit;
        }

        if ((int)$reward['is_used'] === 1) {
            echo json_encode(['success' => false, 'message' => 'Промокод уже использован']);
            exit;
        }

        $now = new DateTime();
        $expiresAt = new DateTime($reward['expires_at']);
        if ($now > $expiresAt) {
            echo json_encode(['success' => false, 'message' => 'Промокод истек']);
            exit;
        }

        // Считаем сумму товаров в корзине, на которые действует скидка
        $eligibleTotal = 0.0;
        $rewardType = (string)$reward['reward_type'];
        $targetId = (int)$reward['target_id'];

        if ($rewardType === 'product') {
            $stmtEligible = $pdo->prepare("
                SELECT SUM(p.price * c.quantity) AS s
                FROM cart c
                JOIN products p ON p.id = c.product_id
                WHERE c.user_id = ? AND c.product_id = ?
            ");
            $stmtEligible->execute([(int)$_SESSION['user_id'], $targetId]);
            $eligibleTotal = (float)($stmtEligible->fetchColumn() ?: 0);
        } elseif ($rewardType === 'brand') {
            $stmtEligible = $pdo->prepare("
                SELECT SUM(p.price * c.quantity) AS s
                FROM cart c
                JOIN products p ON p.id = c.product_id
                WHERE c.user_id = ? AND p.brand_id = ?
            ");
            $stmtEligible->execute([(int)$_SESSION['user_id'], $targetId]);
            $eligibleTotal = (float)($stmtEligible->fetchColumn() ?: 0);
        } elseif ($rewardType === 'category') {
            $stmtEligible = $pdo->prepare("
                SELECT SUM(p.price * c.quantity) AS s
                FROM cart c
                JOIN products p ON p.id = c.product_id
                WHERE c.user_id = ? AND p.category_id = ?
            ");
            $stmtEligible->execute([(int)$_SESSION['user_id'], $targetId]);
            $eligibleTotal = (float)($stmtEligible->fetchColumn() ?: 0);
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверный тип скидки']);
            exit;
        }

        if ($eligibleTotal <= 0) {
            echo json_encode(['success' => false, 'message' => 'В корзине нет товаров, подходящих под этот промокод']);
            exit;
        }

        $percent = max(1, min(90, (int)$reward['discount_percent']));
        $discount = ($eligibleTotal * $percent) / 100;
        if ($discount > $order_total) {
            $discount = $order_total;
        }
        $final_total = $order_total - $discount;

        echo json_encode([
            'success' => true,
            'promo_code' => $reward['promo_code'],
            'promo_id' => (int)$reward['id'],
            'promo_source' => 'wheel',
            'description' => ($rewardType === 'brand' ? 'Скидка на бренд: ' : ($rewardType === 'category' ? 'Скидка на категорию: ' : 'Скидка на товар: ')) . $reward['target_name'],
            'discount_type' => 'percentage',
            'discount_value' => $percent,
            'discount' => round($discount, 2),
            'order_total' => round($order_total, 2),
            'final_total' => round($final_total, 2)
        ]);
        exit;
    }

    // Проверяем существование таблицы
    try {
        $stmt_check_table = $pdo->query("SHOW TABLES LIKE 'promo_codes'");
        if ($stmt_check_table->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Таблица промокодов не найдена. Выполните миграцию БД.']);
            exit;
        }
    } catch (PDOException $e) {
        error_log('Table check error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка проверки таблицы. Убедитесь, что миграция выполнена.']);
        exit;
    }
    
    // Проверяем промокод
    $stmt = $pdo->prepare("
        SELECT * FROM promo_codes 
        WHERE code = ? AND is_active = 1
    ");
    $stmt->execute([$promo_code]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$promo) {
        echo json_encode(['success' => false, 'message' => 'Промокод не найден или неактивен']);
        exit;
    }
    
    // Проверяем даты действия
    $now = new DateTime();
    $valid_from = new DateTime($promo['valid_from']);
    $valid_until = $promo['valid_until'] ? new DateTime($promo['valid_until']) : null;
    
    if ($now < $valid_from) {
        echo json_encode(['success' => false, 'message' => 'Промокод еще не действует']);
        exit;
    }
    
    if ($valid_until && $now > $valid_until) {
        echo json_encode(['success' => false, 'message' => 'Промокод истек']);
        exit;
    }
    
    // Проверяем минимальную сумму заказа
    if ($order_total < $promo['min_order_amount']) {
        $min_amount = number_format($promo['min_order_amount'], 0, ',', ' ');
        echo json_encode(['success' => false, 'message' => "Минимальная сумма заказа для применения промокода: {$min_amount} Р"]);
        exit;
    }
    
    // Проверяем максимальное количество использований
    if ($promo['max_uses'] !== null && $promo['current_uses'] >= $promo['max_uses']) {
        echo json_encode(['success' => false, 'message' => 'Промокод больше не может быть использован']);
        exit;
    }
    
    // Проверяем, использовал ли пользователь уже этот промокод
    // (пока не ограничиваем - можно использовать несколько раз)
    // Если нужно ограничить использование одним пользователем, раскомментируйте:
    /*
    $stmt_check = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM promo_code_uses 
        WHERE promo_code_id = ? AND user_id = ?
    ");
    $stmt_check->execute([$promo['id'], $_SESSION['user_id']]);
    $user_uses_result = $stmt_check->fetch(PDO::FETCH_ASSOC);
    $user_uses = $user_uses_result ? (int)$user_uses_result['count'] : 0;
    */
    
    // Вычисляем скидку
    $discount = 0;
    if ($promo['discount_type'] === 'percentage') {
        $discount = ($order_total * $promo['discount_value']) / 100;
        if ($promo['max_discount'] !== null && $discount > $promo['max_discount']) {
            $discount = $promo['max_discount'];
        }
    } else {
        $discount = $promo['discount_value'];
        if ($discount > $order_total) {
            $discount = $order_total;
        }
    }
    
    $final_total = $order_total - $discount;
    
    echo json_encode([
        'success' => true,
        'promo_code' => $promo['code'],
        'promo_id' => $promo['id'],
        'description' => $promo['description'],
        'discount_type' => $promo['discount_type'],
        'discount_value' => floatval($promo['discount_value']),
        'discount' => round($discount, 2),
        'order_total' => round($order_total, 2),
        'final_total' => round($final_total, 2)
    ]);
    
} catch (PDOException $e) {
    error_log('Promo code check PDO error: ' . $e->getMessage());
    error_log('SQL State: ' . $e->getCode());
    // В режиме разработки показываем детали ошибки
    $error_message = 'Ошибка базы данных';
    if (ini_get('display_errors')) {
        $error_message .= ': ' . $e->getMessage();
    }
    echo json_encode(['success' => false, 'message' => $error_message]);
} catch (Exception $e) {
    error_log('Promo code check error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    // В режиме разработки показываем детали ошибки
    $error_message = 'Ошибка при проверке промокода';
    if (ini_get('display_errors')) {
        $error_message .= ': ' . $e->getMessage();
    }
    echo json_encode(['success' => false, 'message' => $error_message]);
}

