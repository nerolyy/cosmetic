<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_id = !empty($_POST['shop_id']) ? (int)$_POST['shop_id'] : null;
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // Валидация: должен быть выбран магазин ИЛИ указан адрес доставки
    if (empty($shop_id) && empty($delivery_address)) {
        $_SESSION['error'] = 'Выберите магазин для самовывоза или укажите адрес для доставки';
        header('Location: ' . BASE_URL . 'profile.php?section=addresses');
        exit;
    }
    
    try {
        // Если это адрес по умолчанию, снимаем флаг с других адресов
        if ($is_default) {
            $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
        
        // Сохраняем адрес
        $stmt = $pdo->prepare("
            INSERT INTO user_addresses (user_id, shop_id, delivery_address, is_default)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $shop_id ?: null,
            !empty($delivery_address) ? $delivery_address : null,
            $is_default
        ]);
        
        $_SESSION['success'] = 'Адрес успешно сохранен';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка при сохранении адреса: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Неверный метод запроса';
}

header('Location: ' . BASE_URL . 'profile.php?section=addresses');
exit;

