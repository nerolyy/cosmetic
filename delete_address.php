<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['address_id'])) {
    $address_id = (int)$_POST['address_id'];
    
    try {
        // Проверяем, что адрес принадлежит текущему пользователю
        $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$address_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'Адрес успешно удален';
        } else {
            $_SESSION['error'] = 'Адрес не найден';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка при удалении адреса: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Неверный запрос';
}

header('Location: ' . BASE_URL . 'profile.php?section=addresses');
exit;

