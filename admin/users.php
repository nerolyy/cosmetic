<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$error = '';
$success = '';

// Получение данных для редактирования
$edit_id = null;
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();
    if (!$edit_user) {
        $edit_id = null;
    }
}

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email)) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            if ($edit_id) {
                // Редактирование
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, role = ?, password = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $phone, $role, $password_hash, $edit_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $phone, $role, $edit_id]);
                }
                $success = 'Пользователь обновлен';
            } else {
                // Добавление
                if (empty($password)) {
                    $error = 'Укажите пароль для нового пользователя';
                } else {
                    // Проверка на существующий email
                    $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt_check->execute([$email]);
                    if ($stmt_check->fetch()) {
                        $error = 'Пользователь с таким email уже существует';
                    } else {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $email, $phone, $role, $password_hash]);
                        $success = 'Пользователь добавлен';
                    }
                }
            }
            
            if ($success) {
                $edit_id = null;
                $edit_user = null;
            }
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Нельзя удалить самого себя
    if ($id == $_SESSION['user_id']) {
        $error = 'Нельзя удалить собственный аккаунт';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Пользователь удален';
        } catch (PDOException $e) {
            $error = 'Ошибка удаления: ' . $e->getMessage();
        }
    }
}

// Получаем всех пользователей с количеством заказов
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(o.id) as orders_count
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

$page_title = 'Управление пользователями';
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h2>Админ-панель</h2>
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php" class="admin-nav-link">Главная</a></li>
                <li><a href="products.php" class="admin-nav-link">Товары</a></li>
                <li><a href="categories.php" class="admin-nav-link">Категории</a></li>
                <li><a href="brands.php" class="admin-nav-link">Бренды</a></li>
                <li><a href="orders.php" class="admin-nav-link">Заказы</a></li>
                <li><a href="users.php" class="admin-nav-link active">Пользователи</a></li>
                <li><a href="shops.php" class="admin-nav-link">Магазины</a></li>
                <li><a href="<?php echo BASE_URL; ?>" class="admin-nav-link">На сайт</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Управление пользователями</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Форма добавления/редактирования -->
        <div class="admin-form-section">
            <h2><?php echo $edit_id ? 'Редактировать пользователя' : 'Добавить пользователя'; ?></h2>
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label>Имя *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($edit_user['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Телефон</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Роль</label>
                    <select name="role">
                        <option value="user" <?php echo ($edit_user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                        <option value="admin" <?php echo ($edit_user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Пароль <?php echo $edit_id ? '(оставьте пустым, чтобы не менять)' : '*'; ?></label>
                    <input type="password" name="password" <?php echo $edit_id ? '' : 'required'; ?>>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_id ? 'Сохранить' : 'Добавить'; ?></button>
                    <?php if ($edit_id): ?>
                        <a href="users.php" class="btn btn-secondary">Отмена</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Список пользователей -->
        <div class="admin-table-section">
            <h2>Список пользователей</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Роль</th>
                            <th>Заказов</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'admin' : 'user'; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'Админ' : 'Пользователь'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="orders.php?user_id=<?php echo $user['id']; ?>">
                                        <?php echo $user['orders_count']; ?>
                                    </a>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $user['id']; ?>" class="btn-edit">Редактировать</a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete=<?php echo $user['id']; ?>" 
                                           onclick="return confirm('Удалить пользователя?')" 
                                           class="btn-delete">Удалить</a>
                                    <?php endif; ?>
                                    <a href="orders.php?user_id=<?php echo $user['id']; ?>" class="btn-view">Заказы</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

