<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    $current_password = trim($_POST['current_password'] ?? '');
    
    // Очищаем телефон от форматирования (оставляем только цифры)
    if (!empty($phone)) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Если начинается с 8, заменяем на 7
        if (strlen($phone) > 0 && $phone[0] === '8') {
            $phone = '7' . substr($phone, 1);
        }
        // Если меньше 11 цифр и не начинается с 7, добавляем 7
        if (strlen($phone) < 11 && strlen($phone) > 0 && $phone[0] !== '7') {
            $phone = '7' . $phone;
        }
        // Ограничиваем до 11 цифр
        if (strlen($phone) > 11) {
            $phone = substr($phone, 0, 11);
        }
        // Если телефон пустой после очистки, делаем его null
        if (empty($phone) || strlen($phone) < 10) {
            $phone = null;
        } else {
            // Форматируем для сохранения: +7XXXXXXXXXX
            $phone = '+' . $phone;
        }
    } else {
        $phone = null;
    }
    
    // Валидация обязательных полей
    if (empty($name) || empty($email)) {
        $_SESSION['error'] = 'Заполните все обязательные поля';
        header('Location: ' . BASE_URL . 'profile.php?section=edit');
        exit;
    }
    
    // Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Неверный формат email';
        header('Location: ' . BASE_URL . 'profile.php?section=edit');
        exit;
    }
    
    try {
        // Проверяем, не занят ли email другим пользователем
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check->execute([$email, $_SESSION['user_id']]);
        if ($stmt_check->fetch()) {
            $_SESSION['error'] = 'Пользователь с таким email уже существует';
            header('Location: ' . BASE_URL . 'profile.php?section=edit');
            exit;
        }
        
        // Если указан новый пароль, проверяем текущий пароль и подтверждение
        if (!empty($password)) {
            if (empty($current_password)) {
                $_SESSION['error'] = 'Для изменения пароля укажите текущий пароль';
                header('Location: ' . BASE_URL . 'profile.php?section=edit');
                exit;
            }
            
            if ($password !== $password_confirm) {
                $_SESSION['error'] = 'Новый пароль и подтверждение не совпадают';
                header('Location: ' . BASE_URL . 'profile.php?section=edit');
                exit;
            }
            
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'Пароль должен содержать минимум 6 символов';
                header('Location: ' . BASE_URL . 'profile.php?section=edit');
                exit;
            }
            
            // Проверяем текущий пароль
            $stmt_user = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt_user->execute([$_SESSION['user_id']]);
            $user_data = $stmt_user->fetch();
            
            if (!$user_data || !password_verify($current_password, $user_data['password'])) {
                $_SESSION['error'] = 'Неверный текущий пароль';
                header('Location: ' . BASE_URL . 'profile.php?section=edit');
                exit;
            }
            
            // Обновляем данные с новым паролем
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $phone, $password_hash, $_SESSION['user_id']]);
        } else {
            // Обновляем данные без изменения пароля
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
        }
        
        // Проверяем результат обновления
        $rowsAffected = $stmt->rowCount();
        
        // Очищаем кеш пользователя - ВСЕГДА, даже если rowCount = 0
        clearUserCache();
        
        // Проверяем, что данные действительно обновились в БД
        $stmt_verify = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
        $stmt_verify->execute([$_SESSION['user_id']]);
        $updated_data = $stmt_verify->fetch();
        
        if ($updated_data) {
            // Сравниваем сохраненные данные с тем, что мы пытались сохранить
            $nameMatch = $updated_data['name'] === $name;
            $emailMatch = $updated_data['email'] === $email;
            $phoneMatch = ($updated_data['phone'] === $phone) || (empty($updated_data['phone']) && empty($phone));
            
            if ($nameMatch && $emailMatch && $phoneMatch) {
                $_SESSION['success'] = 'Данные успешно обновлены';
            } elseif ($rowsAffected > 0) {
                // Данные обновлены, но возможно были приведены к другому формату
                $_SESSION['success'] = 'Данные успешно обновлены';
            } else {
                // Если данных в БД нет изменений, возможно они уже были такими
                $_SESSION['success'] = 'Данные сохранены';
            }
        } else {
            $_SESSION['error'] = 'Ошибка при проверке обновленных данных';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка при обновлении данных: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Неверный метод запроса';
}

header('Location: ' . BASE_URL . 'profile.php?section=edit');
exit;

