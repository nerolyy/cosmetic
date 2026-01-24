<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $brand_id = (int)($_POST['brand_id'] ?? 0);
    
    if (!$brand_id) {
        echo json_encode(['success' => false, 'message' => 'Неверный ID бренда']);
        exit;
    }
    
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT IGNORE INTO brand_favorites (user_id, brand_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $brand_id]);
            echo json_encode(['success' => true, 'message' => 'Бренд добавлен в избранное']);
        } elseif ($action === 'remove') {
            $stmt = $pdo->prepare("DELETE FROM brand_favorites WHERE user_id = ? AND brand_id = ?");
            $stmt->execute([$_SESSION['user_id'], $brand_id]);
            echo json_encode(['success' => true, 'message' => 'Бренд удален из избранного']);
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



