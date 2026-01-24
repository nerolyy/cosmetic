<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
        exit;
    }
    
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            echo json_encode(['success' => true, 'message' => 'Товар добавлен в избранное']);
        } elseif ($action === 'remove') {
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            echo json_encode(['success' => true, 'message' => 'Товар удален из избранного']);
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



