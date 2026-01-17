<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$error = '';
$success = '';
$edit_id = null;
$edit_category = null;

// Обработка добавления категории
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $is_hidden = isset($_POST['is_hidden']) ? 1 : 0;
    
    if (empty($name) || empty($slug)) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, parent_id, is_hidden) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $parent_id, $is_hidden]);
            $success = 'Категория успешно добавлена';
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Обработка редактирования категории
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $is_hidden = isset($_POST['is_hidden']) ? 1 : 0;
    
    if (empty($name) || empty($slug)) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, parent_id = ?, is_hidden = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $parent_id, $is_hidden, $id]);
            $success = 'Категория успешно обновлена';
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Получение данных для редактирования
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch();
    if (!$edit_category) {
        $edit_id = null;
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Категория удалена';
    } catch (PDOException $e) {
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

// Получаем все категории
$stmt = $pdo->query("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.parent_id, c.name");
$categories = $stmt->fetchAll();

// Получаем категории для выпадающего списка
$stmt_parents = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
$parent_categories = $stmt_parents->fetchAll();

$page_title = 'Управление категориями';
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h2>Админ-панель</h2>
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php" class="admin-nav-link">Главная</a></li>
                <li><a href="products.php" class="admin-nav-link">Товары</a></li>
                <li><a href="categories.php" class="admin-nav-link active">Категории</a></li>
                <li><a href="brands.php" class="admin-nav-link">Бренды</a></li>
                <li><a href="orders.php" class="admin-nav-link">Заказы</a></li>
                <li><a href="users.php" class="admin-nav-link">Пользователи</a></li>
                <li><a href="shops.php" class="admin-nav-link">Магазины</a></li>
                <li><a href="<?php echo BASE_URL; ?>" class="admin-nav-link">На сайт</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Управление категориями</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Форма добавления/редактирования -->
        <div class="admin-form-section">
            <h2><?php echo $edit_id ? 'Редактировать категорию' : 'Добавить категорию'; ?></h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="<?php echo $edit_id ? 'edit' : 'add'; ?>">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($edit_category['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Slug (английскими буквами, без пробелов) *</label>
                    <input type="text" name="slug" required pattern="[a-z0-9-]+" value="<?php echo htmlspecialchars($edit_category['slug'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Родительская категория</label>
                    <select name="parent_id">
                        <option value="">Без родительской категории</option>
                        <?php foreach ($parent_categories as $parent): ?>
                            <?php if ($parent['id'] != $edit_id): ?>
                                <option value="<?php echo $parent['id']; ?>" <?php echo ($edit_category && $edit_category['parent_id'] == $parent['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($parent['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_hidden" value="1" <?php echo ($edit_category && $edit_category['is_hidden']) ? 'checked' : ''; ?>>
                        Скрыть категорию
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_id ? 'Сохранить' : 'Добавить'; ?></button>
                    <?php if ($edit_id): ?>
                        <a href="categories.php" class="btn btn-secondary">Отмена</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Список категорий -->
        <div class="admin-table-section">
            <h2>Список категорий</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Родительская</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo htmlspecialchars($category['parent_name'] ?? '-'); ?></td>
                            <td>
                                <?php if (!empty($category['is_hidden'])): ?>
                                    <span class="badge badge-hidden">Скрыта</span>
                                <?php else: ?>
                                    <span class="badge badge-visible">Видима</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-edit btn-sm">Редактировать</a>
                                <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить категорию?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

