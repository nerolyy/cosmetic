<?php
require_once 'config.php';

// Если пользователь уже авторизован, перенаправляем
if (isLoggedIn()) {
    header('Location: ' . BASE_URL);
    exit;
}

$error = '';
$success = '';

if (isset($_GET['success'])) {
    $success = 'Регистрация прошла успешно! Теперь вы можете войти.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Перенаправление
            $redirect = $_GET['redirect'] ?? BASE_URL;
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}

$page_title = 'Вход';
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Вход</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Войти</button>
        </form>

        <p class="auth-link">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


