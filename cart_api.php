<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
        exit;
    }
    
    try {
        if ($action === 'add') {
            // Проверяем, есть ли уже товар в корзине
            $stmt_check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt_check->execute([$_SESSION['user_id'], $product_id]);
            $existing = $stmt_check->fetch();
            
            if ($existing) {
                // Обновляем количество
                $new_quantity = $existing['quantity'] + $quantity;
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$new_quantity, $existing['id']]);
                echo json_encode(['success' => true, 'message' => 'Количество товара обновлено', 'quantity' => $new_quantity]);
            } else {
                // Добавляем новый товар
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
                echo json_encode(['success' => true, 'message' => 'Товар добавлен в корзину', 'quantity' => $quantity]);
            }
        } elseif ($action === 'update') {
            if ($quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Количество должно быть больше 0']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
            echo json_encode(['success' => true, 'message' => 'Количество обновлено', 'quantity' => $quantity]);
        } elseif ($action === 'remove') {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            echo json_encode(['success' => true, 'message' => 'Товар удален из корзины']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверное действие']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>

