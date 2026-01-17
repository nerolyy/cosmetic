<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$error = '';
$success = '';
$edit_id = null;
$edit_brand = null;

// Обработка добавления бренда
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    
    if (empty($name) || empty($slug)) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO brands (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            $success = 'Бренд успешно добавлен';
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Обработка редактирования бренда
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    
    if (empty($name) || empty($slug)) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, slug = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $id]);
            $success = 'Бренд успешно обновлен';
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Получение данных для редактирования
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_brand = $stmt->fetch();
    if (!$edit_brand) {
        $edit_id = null;
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Бренд удален';
    } catch (PDOException $e) {
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

// Получаем все бренды
$stmt = $pdo->query("SELECT * FROM brands ORDER BY name");
$brands = $stmt->fetchAll();

$page_title = 'Управление брендами';
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
                <li><a href="brands.php" class="admin-nav-link active">Бренды</a></li>
                <li><a href="orders.php" class="admin-nav-link">Заказы</a></li>
                <li><a href="users.php" class="admin-nav-link">Пользователи</a></li>
                <li><a href="shops.php" class="admin-nav-link">Магазины</a></li>
                <li><a href="<?php echo BASE_URL; ?>" class="admin-nav-link">На сайт</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Управление брендами</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Форма добавления/редактирования -->
        <div class="admin-form-section">
            <h2><?php echo $edit_id ? 'Редактировать бренд' : 'Добавить бренд'; ?></h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="<?php echo $edit_id ? 'edit' : 'add'; ?>">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($edit_brand['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Slug (английскими буквами, без пробелов) *</label>
                    <input type="text" name="slug" required pattern="[a-z0-9-]+" value="<?php echo htmlspecialchars($edit_brand['slug'] ?? ''); ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_id ? 'Сохранить' : 'Добавить'; ?></button>
                    <?php if ($edit_id): ?>
                        <a href="brands.php" class="btn btn-secondary">Отмена</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Список брендов -->
        <div class="admin-table-section">
            <h2>Список брендов</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($brands as $brand): ?>
                        <tr>
                            <td><?php echo $brand['id']; ?></td>
                            <td><?php echo htmlspecialchars($brand['name']); ?></td>
                            <td><?php echo htmlspecialchars($brand['slug']); ?></td>
                            <td>
                                <a href="?edit=<?php echo $brand['id']; ?>" class="btn btn-edit btn-sm">Редактировать</a>
                                <a href="?delete=<?php echo $brand['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить бренд?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>



