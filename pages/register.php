<?php
require_once __DIR__ . '/../config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Валидация
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Заполните все обязательные поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } else {
        // Проверка существующего пользователя
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким email уже существует';
        } else {
            // Регистрация
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Валидация роли
            if (!in_array($role, ['user', 'admin'])) {
                $role = 'user';
            }
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $hashed_password, $role])) {
                $success = 'Регистрация успешна! Теперь вы можете войти.';
                header('Location: login.php?success=1');
                exit;
            } else {
                $error = 'Ошибка при регистрации';
            }
        }
    }
}

$page_title = 'Регистрация';
include __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Регистрация</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Имя *</label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="role">Роль *</label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo (($_POST['role'] ?? 'user') === 'user') ? 'selected' : ''; ?>>Пользователь</option>
                    <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Администратор</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Пароль *</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="password_confirm">Подтвердите пароль *</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
        </form>

        <p class="auth-link">
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>



