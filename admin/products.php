<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$error = '';
$success = '';
$edit_id = null;
$edit_product = null;

// Функция загрузки изображения
function uploadImage($file, $upload_dir = '../uploads/') {
    if (!isset($file['error']) || is_array($file['error'])) {
        return null;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowed_extensions)) {
        return null;
    }
    
    $filename = uniqid() . '.' . $extension;
    $destination = $upload_dir . $filename;
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    
    return null;
}

// Обработка добавления товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = !empty($_POST['price']) ? (float)$_POST['price'] : 0;
    $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null;
    $discount = !empty($_POST['discount']) ? (int)$_POST['discount'] : 0;
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $stock = !empty($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image']);
    }
    
    if (empty($name) || empty($slug) || $price <= 0) {
        $error = 'Заполните все обязательные поля (название, slug, цена)';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, old_price, discount, image, category_id, brand_id, stock, is_new, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $price, $old_price, $discount, $image, $category_id, $brand_id, $stock, $is_new, $is_featured]);
            $success = 'Товар успешно добавлен';
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Обработка редактирования товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = !empty($_POST['price']) ? (float)$_POST['price'] : 0;
    $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null;
    $discount = !empty($_POST['discount']) ? (int)$_POST['discount'] : 0;
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $stock = !empty($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Получаем текущее изображение
    $stmt_current = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt_current->execute([$id]);
    $current_image = $stmt_current->fetchColumn();
    
    $image = $current_image; // По умолчанию оставляем текущее изображение
    
    // Если загружено новое изображение
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $new_image = uploadImage($_FILES['image']);
        if ($new_image) {
            // Удаляем старое изображение, если оно есть
            if ($current_image && file_exists('../uploads/' . $current_image)) {
                unlink('../uploads/' . $current_image);
            }
            $image = $new_image;
        }
    }
    
    if (empty($name) || empty($slug) || $price <= 0) {
        $error = 'Заполните все обязательные поля (название, slug, цена)';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, slug = ?, description = ?, price = ?, old_price = ?, discount = ?, image = ?, category_id = ?, brand_id = ?, stock = ?, is_new = ?, is_featured = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $price, $old_price, $discount, $image, $category_id, $brand_id, $stock, $is_new, $is_featured, $id]);
            $success = 'Товар успешно обновлен';
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Получение данных для редактирования
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
    if (!$edit_product) {
        $edit_id = null;
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Товар удален';
    } catch (PDOException $e) {
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

// Получаем все товары
$stmt = $pdo->query("
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

// Получаем категории и бренды для форм
$stmt_categories = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt_categories->fetchAll();

$stmt_brands = $pdo->query("SELECT * FROM brands ORDER BY name");
$brands = $stmt_brands->fetchAll();

$page_title = 'Управление товарами';
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h2>Админ-панель</h2>
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php" class="admin-nav-link">Главная</a></li>
                <li><a href="products.php" class="admin-nav-link active">Товары</a></li>
                <li><a href="categories.php" class="admin-nav-link">Категории</a></li>
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
            <h1>Управление товарами</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Форма добавления/редактирования -->
        <div class="admin-form-section">
            <h2><?php echo $edit_id ? 'Редактировать товар' : 'Добавить товар'; ?></h2>
            <form method="POST" class="admin-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_id ? 'edit' : 'add'; ?>">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Название *</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Slug *</label>
                        <input type="text" name="slug" required pattern="[a-z0-9-]+" value="<?php echo htmlspecialchars($edit_product['slug'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Изображение</label>
                    <?php if ($edit_id && !empty($edit_product['image'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($edit_product['image']); ?>" alt="Текущее изображение" style="max-width: 200px; max-height: 200px; border: 1px solid var(--border-color);">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small style="display: block; margin-top: 5px; color: var(--text-light);">Форматы: JPG, PNG, GIF, WebP</small>
                </div>
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="3"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Цена *</label>
                        <input type="number" name="price" step="0.01" min="0" required value="<?php echo $edit_product['price'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Старая цена</label>
                        <input type="number" name="old_price" step="0.01" min="0" value="<?php echo $edit_product['old_price'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Скидка (%)</label>
                        <input type="number" name="discount" min="0" max="100" value="<?php echo $edit_product['discount'] ?? '0'; ?>">
                    </div>
                    <div class="form-group">
                        <label>Остаток</label>
                        <input type="number" name="stock" min="0" value="<?php echo $edit_product['stock'] ?? '0'; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Категория</label>
                        <select name="category_id">
                            <option value="">Без категории</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Бренд</label>
                        <select name="brand_id">
                            <option value="">Без бренда</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo ($edit_product && $edit_product['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_new" value="1" <?php echo ($edit_product && $edit_product['is_new']) ? 'checked' : ''; ?>> Новинка
                    </label>
                    <label>
                        <input type="checkbox" name="is_featured" value="1" <?php echo ($edit_product && $edit_product['is_featured']) ? 'checked' : ''; ?>> Хит продаж
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_id ? 'Сохранить' : 'Добавить товар'; ?></button>
                    <?php if ($edit_id): ?>
                        <a href="products.php" class="btn btn-secondary">Отмена</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Список товаров -->
        <div class="admin-table-section">
            <h2>Список товаров</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Фото</th>
                        <th>Название</th>
                        <th>Цена</th>
                        <th>Категория</th>
                        <th>Бренд</th>
                        <th>Остаток</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="admin-product-image">
                                <?php else: ?>
                                    <span class="admin-no-image">Нет фото</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['price'], 0, ',', ' '); ?> Р</td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($product['brand_name'] ?? '-'); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td>
                                <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-edit btn-sm">Редактировать</a>
                                <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить товар?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

