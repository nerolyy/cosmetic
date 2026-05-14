<?php
require_once __DIR__ . '/../config/config.php';

// Не выводить HTML/notice вместе с JSON — иначе браузер не сможет разобрать ответ
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

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
    $promo_code_id = !empty($_POST['promo_code_id']) ? (int)$_POST['promo_code_id'] : null;
    $wheel_reward_id = !empty($_POST['wheel_reward_id']) ? (int)$_POST['wheel_reward_id'] : null;
    
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

    // DDL (CREATE TABLE) в MySQL делает неявный COMMIT — только до beginTransaction()
    wheelEnsureTables();

    try {
        // Получаем данные текущего пользователя (для email и других полей)
        $user = getCurrentUser();
        if (!$user) {
            throw new Exception('Пользователь не найден');
        }
        
        // Начинаем транзакцию
        $pdo->beginTransaction();
        
        // Получаем товары из корзины с информацией о наличии на складе
        // Используем SELECT FOR UPDATE для блокировки строк и предотвращения конкурентных заказов
        $stmt = $pdo->prepare("
            SELECT c.*, p.price, p.stock, p.name as product_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
            FOR UPDATE
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll();

        if (empty($cart_items)) {
            $stmtCnt = $pdo->prepare('SELECT COUNT(*) FROM cart WHERE user_id = ?');
            $stmtCnt->execute([(int) $_SESSION['user_id']]);
            $rawCartCount = (int) $stmtCnt->fetchColumn();
            if ($rawCartCount > 0) {
                throw new Exception(
                    'В корзине есть позиции, но они не отображаются в заказе (товар удалён из каталога или ошибка связи). Обновите страницу корзины и удалите проблемные товары.'
                );
            }
            throw new Exception('Корзина пуста. Обновите страницу (F5). Если товары снова в корзине — повторите оформление.');
        }
        
        // Проверяем наличие товаров на складе
        $out_of_stock_items = [];
        foreach ($cart_items as $item) {
            if ($item['stock'] < $item['quantity']) {
                $out_of_stock_items[] = [
                    'name' => $item['product_name'],
                    'requested' => $item['quantity'],
                    'available' => $item['stock']
                ];
            }
        }
        
        if (!empty($out_of_stock_items)) {
            $messages = [];
            foreach ($out_of_stock_items as $item) {
                if ($item['available'] <= 0) {
                    $messages[] = "Товар '{$item['name']}' отсутствует на складе";
                } else {
                    $messages[] = "Товар '{$item['name']}': запрошено {$item['requested']} шт., доступно {$item['available']} шт.";
                }
            }
            throw new Exception('Недостаточно товаров на складе: ' . implode('; ', $messages));
        }
        
        // Вычисляем общую стоимость
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        // Применяем промокод, если указан
        $promo_discount = 0;
        $wheel_reward = null;
        if ($promo_code_id) {
            $stmt_promo = $pdo->prepare("
                SELECT * FROM promo_codes 
                WHERE id = ? AND is_active = 1
            ");
            $stmt_promo->execute([$promo_code_id]);
            $promo = $stmt_promo->fetch();
            
            if ($promo) {
                // Проверяем даты действия
                $now = new DateTime();
                $valid_from = new DateTime($promo['valid_from']);
                $valid_until = $promo['valid_until'] ? new DateTime($promo['valid_until']) : null;
                
                $is_valid = true;
                if ($now < $valid_from) $is_valid = false;
                if ($valid_until && $now > $valid_until) $is_valid = false;
                
                // Проверяем минимальную сумму
                if ($subtotal < $promo['min_order_amount']) $is_valid = false;
                
                // Проверяем максимальное количество использований
                if ($promo['max_uses'] !== null && $promo['current_uses'] >= $promo['max_uses']) $is_valid = false;
                
                if ($is_valid) {
                    // Вычисляем скидку
                    if ($promo['discount_type'] === 'percentage') {
                        $promo_discount = ($subtotal * $promo['discount_value']) / 100;
                        if ($promo['max_discount'] !== null && $promo_discount > $promo['max_discount']) {
                            $promo_discount = $promo['max_discount'];
                        }
                    } else {
                        $promo_discount = $promo['discount_value'];
                        if ($promo_discount > $subtotal) {
                            $promo_discount = $subtotal;
                        }
                    }
                    
                    // Увеличиваем счетчик использований
                    $stmt_update = $pdo->prepare("
                        UPDATE promo_codes 
                        SET current_uses = current_uses + 1 
                        WHERE id = ?
                    ");
                    $stmt_update->execute([$promo_code_id]);
                } else {
                    $promo_code_id = null; // Промокод недействителен
                }
            } else {
                $promo_code_id = null; // Промокод не найден
            }
        }

        // Применяем скидку колеса фортуны (если указана)
        if ($wheel_reward_id) {
            $stmtReward = $pdo->prepare("
                SELECT id, user_id, reward_type, target_id, target_name, discount_percent, promo_code, is_used, expires_at
                FROM wheel_rewards
                WHERE id = ? AND user_id = ?
                LIMIT 1
            ");
            $stmtReward->execute([$wheel_reward_id, (int)$_SESSION['user_id']]);
            $wheel_reward = $stmtReward->fetch(PDO::FETCH_ASSOC);

            if ($wheel_reward && (int)$wheel_reward['is_used'] === 0) {
                $now = new DateTime();
                $expiresAt = new DateTime($wheel_reward['expires_at']);
                if ($now <= $expiresAt) {
                    $eligibleTotal = 0.0;
                    $rewardType = (string)$wheel_reward['reward_type'];
                    $targetId = (int)$wheel_reward['target_id'];

                    foreach ($cart_items as $item) {
                        $line = $item['price'] * $item['quantity'];

                        if ($rewardType === 'product' && (int)$item['product_id'] === $targetId) {
                            $eligibleTotal += $line;
                        } elseif ($rewardType === 'brand' || $rewardType === 'category') {
                            // Для brand/category нужно узнать принадлежность товара
                            // (достанем одним запросом позже)
                        }
                    }

                    if ($rewardType === 'brand' || $rewardType === 'category') {
                        $productIds = array_map(static fn($it) => (int)$it['product_id'], $cart_items);
                        $productIds = array_values(array_unique($productIds));
                        if (!empty($productIds)) {
                            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                            $stmtMeta = $pdo->prepare("
                                SELECT id, brand_id, category_id
                                FROM products
                                WHERE id IN ($placeholders)
                            ");
                            $stmtMeta->execute($productIds);
                            $meta = [];
                            foreach ($stmtMeta->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                $meta[(int)$row['id']] = $row;
                            }

                            foreach ($cart_items as $item) {
                                $pid = (int)$item['product_id'];
                                if (!isset($meta[$pid])) {
                                    continue;
                                }
                                $line = $item['price'] * $item['quantity'];
                                if ($rewardType === 'brand' && (int)$meta[$pid]['brand_id'] === $targetId) {
                                    $eligibleTotal += $line;
                                }
                                if ($rewardType === 'category' && (int)$meta[$pid]['category_id'] === $targetId) {
                                    $eligibleTotal += $line;
                                }
                            }
                        }
                    }

                    if ($eligibleTotal > 0) {
                        $percent = max(1, min(90, (int)$wheel_reward['discount_percent']));
                        $wheelDiscount = ($eligibleTotal * $percent) / 100;
                        if ($wheelDiscount > ($subtotal - $promo_discount)) {
                            $wheelDiscount = ($subtotal - $promo_discount);
                        }
                        $promo_discount += max(0, $wheelDiscount);

                        // Помечаем промокод колеса использованным
                        $stmtUseWheel = $pdo->prepare("UPDATE wheel_rewards SET is_used = 1 WHERE id = ? AND user_id = ? AND is_used = 0");
                        $stmtUseWheel->execute([(int)$wheel_reward['id'], (int)$_SESSION['user_id']]);
                    } else {
                        $wheel_reward_id = null;
                    }
                } else {
                    $wheel_reward_id = null;
                }
            } else {
                $wheel_reward_id = null;
            }
        }
        
        $total = max(0, $subtotal - $promo_discount);
        
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
        
        // Поля промокода
        if (in_array('promo_code_id', $columns) && $promo_code_id) {
            $fields[] = 'promo_code_id';
            $values[] = $promo_code_id;
            $placeholders[] = '?';
        }
        
        if (in_array('promo_code_discount', $columns)) {
            $fields[] = 'promo_code_discount';
            $values[] = $promo_discount;
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
            
            // Вычитаем количество товара из остатка на складе
            $stmt_update_stock = $pdo->prepare("
                UPDATE products 
                SET stock = stock - ? 
                WHERE id = ? AND stock >= ?
            ");
            $stmt_update_stock->execute([
                $item['quantity'],
                $item['product_id'],
                $item['quantity']
            ]);
            
            // Проверяем, что обновление прошло успешно
            if ($stmt_update_stock->rowCount() === 0) {
                throw new Exception("Не удалось обновить остаток товара '{$item['product_name']}' на складе. Возможно, товар закончился.");
            }
        }
        
        // Очищаем корзину
        $stmt_clear = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_clear->execute([$_SESSION['user_id']]);
        
        // Записываем использование промокода, если был применен
        if ($promo_code_id && $promo_discount > 0) {
            $stmt_use = $pdo->prepare("
                INSERT INTO promo_code_uses (promo_code_id, user_id, order_id)
                VALUES (?, ?, ?)
            ");
            $stmt_use->execute([$promo_code_id, $_SESSION['user_id'], $order_id]);
        }

        // Бонус за покупку: +1 прокрутка, если сумма заказа от 1000 руб.
        if ($total >= 1000) {
            wheelAddSpins((int)$_SESSION['user_id'], 1, 'order_over_1000', (int)$order_id);
        }
        
        // Подтверждаем транзакцию (после DDL внутри запроса транзакция могла уже закрыться неявным COMMIT)
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Заказ успешно оформлен',
            'order_id' => $order_id
        ]);
        
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            try {
                $pdo->rollBack();
            } catch (Throwable $e2) {
                error_log('create_order rollBack: ' . $e2->getMessage());
            }
        }
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при создании заказа: ' . $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>

