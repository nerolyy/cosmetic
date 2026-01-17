<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $recipient_phone = trim($_POST['recipient_phone'] ?? '');
    $delivery_method = $_POST['delivery_method'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $shop_id = !empty($_POST['shop_id']) ? (int)$_POST['shop_id'] : null;
    
    $errors = [];
    
    // Валидация имени
    if (empty($recipient_name)) {
        $errors['name'] = 'Укажите имя получателя';
    }
    
    // Валидация телефона
    if (empty($recipient_phone)) {
        $errors['phone'] = 'Укажите номер телефона';
    } else {
        // Оставляем только цифры
        $phone_digits = preg_replace('/[^0-9]/', '', $recipient_phone);
        // Если начинается с 8, заменяем на 7
        if (strlen($phone_digits) > 0 && $phone_digits[0] === '8') {
            $phone_digits = '7' . substr($phone_digits, 1);
        }
        // Если не начинается с 7, добавляем 7
        if (strlen($phone_digits) > 0 && $phone_digits[0] !== '7') {
            $phone_digits = '7' . $phone_digits;
        }
        // Проверяем формат российского номера (11 цифр, начинается с 7)
        if (strlen($phone_digits) !== 11 || $phone_digits[0] !== '7') {
            $errors['phone'] = 'Некорректный номер телефона. Введите российский номер (10 цифр)';
        }
    }
    
    // Валидация способа доставки
    if (empty($delivery_method) || !in_array($delivery_method, ['courier', 'pickup'])) {
        $errors['delivery'] = 'Выберите способ доставки';
    }
    
    // Если курьер - обязателен адрес
    if ($delivery_method === 'courier' && empty($address)) {
        $errors['address'] = 'Укажите адрес доставки';
    }
    
    // Если самовывоз - обязателен магазин
    if ($delivery_method === 'pickup') {
        if (empty($shop_id)) {
            $errors['shop'] = 'Выберите магазин для самовывоза';
        } else {
            // Проверяем существование магазина
            $stmt_shop = $pdo->prepare("SELECT id FROM shops WHERE id = ?");
            $stmt_shop->execute([$shop_id]);
            if (!$stmt_shop->fetch()) {
                $errors['shop'] = 'Выбранный магазин не найден';
            }
        }
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    try {
        // Получаем данные текущего пользователя (для email и других полей)
        $user = getCurrentUser();
        if (!$user) {
            throw new Exception('Пользователь не найден');
        }
        
        // Начинаем транзакцию
        $pdo->beginTransaction();
        
        // Получаем товары из корзины
        $stmt = $pdo->prepare("
            SELECT c.*, p.price
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll();
        
        if (empty($cart_items)) {
            throw new Exception('Корзина пуста');
        }
        
        // Вычисляем общую стоимость
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Рассчитываем дату доставки
        // Курьером: +2-3 дня, самовывоз: +1 день
        $delivery_days = ($delivery_method === 'courier') ? 2 : 1;
        $delivery_date = date('Y-m-d', strtotime("+{$delivery_days} days"));
        
        // Проверяем структуру таблицы orders для определения полей
        $stmt_cols = $pdo->query("SHOW COLUMNS FROM orders");
        $columns = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);
        
        // Формируем список полей и значений для INSERT
        $fields = ['user_id', 'total', 'status'];
        $values = [$_SESSION['user_id'], $total, 'pending'];
        $placeholders = ['?', '?', '?'];
        
        // Добавляем поля, если они существуют в таблице
        // Поле 'email' - если требуется в таблице
        if (in_array('email', $columns)) {
            $fields[] = 'email';
            $values[] = $user['email'] ?? '';
            $placeholders[] = '?';
        }
        
        // Поле 'name' - старое название, если используется в таблице
        if (in_array('name', $columns)) {
            $fields[] = 'name';
            $values[] = $recipient_name;
            $placeholders[] = '?';
        }
        
        // Поле 'phone' - старое название, если используется в таблице
        if (in_array('phone', $columns)) {
            $fields[] = 'phone';
            $values[] = $phone_digits;
            $placeholders[] = '?';
        }
        
        // Новые поля с префиксом recipient_
        if (in_array('recipient_name', $columns)) {
            $fields[] = 'recipient_name';
            $values[] = $recipient_name;
            $placeholders[] = '?';
        }
        
        if (in_array('recipient_phone', $columns)) {
            $fields[] = 'recipient_phone';
            $values[] = $phone_digits;
            $placeholders[] = '?';
        }
        
        if (in_array('delivery_method', $columns)) {
            $fields[] = 'delivery_method';
            $values[] = $delivery_method;
            $placeholders[] = '?';
        }
        
        if (in_array('address', $columns)) {
            $fields[] = 'address';
            $values[] = $delivery_method === 'courier' ? $address : null;
            $placeholders[] = '?';
        }
        
        if (in_array('shop_id', $columns)) {
            $fields[] = 'shop_id';
            $values[] = $delivery_method === 'pickup' ? $shop_id : null;
            $placeholders[] = '?';
        }
        
        if (in_array('delivery_date', $columns)) {
            $fields[] = 'delivery_date';
            $values[] = $delivery_date;
            $placeholders[] = '?';
        }
        
        // Создаем заказ
        $sql = "INSERT INTO orders (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        $order_id = $pdo->lastInsertId();
        
        // Добавляем товары в заказ
        $stmt_items = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($cart_items as $item) {
            $stmt_items->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        // Очищаем корзину
        $stmt_clear = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_clear->execute([$_SESSION['user_id']]);
        
        // Подтверждаем транзакцию
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Заказ успешно оформлен',
            'order_id' => $order_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Ошибка при создании заказа: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>

