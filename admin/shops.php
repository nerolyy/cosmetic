<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$error = '';
$success = '';

// Получение данных для редактирования
$edit_id = null;
$edit_shop = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM shops WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_shop = $stmt->fetch();
    if (!$edit_shop) {
        $edit_id = null;
    }
}

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    $description = trim($_POST['description'] ?? '');
    $how_to_get = trim($_POST['how_to_get'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $work_hours = trim($_POST['work_hours'] ?? '');
    
    if (empty($name) || empty($address)) {
        $error = 'Заполните название и адрес магазина';
    } elseif (is_null($latitude) || is_null($longitude)) {
        $error = 'Укажите координаты магазина (широта и долгота)';
    } else {
        try {
            if ($edit_id) {
                // Редактирование
                $stmt = $pdo->prepare("
                    UPDATE shops 
                    SET name = ?, address = ?, latitude = ?, longitude = ?, 
                        description = ?, how_to_get = ?, phone = ?, work_hours = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $address, $latitude, $longitude, $description, $how_to_get, $phone, $work_hours, $edit_id]);
                $success = 'Магазин обновлен';
            } else {
                // Добавление
                $stmt = $pdo->prepare("
                    INSERT INTO shops (name, address, latitude, longitude, description, how_to_get, phone, work_hours) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $address, $latitude, $longitude, $description, $how_to_get, $phone, $work_hours]);
                $success = 'Магазин добавлен';
            }
            
            if ($success) {
                $edit_id = null;
                $edit_shop = null;
            }
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Проверяем, нет ли заказов с этим магазином
        $stmt_check = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE shop_id = ?");
        $stmt_check->execute([$id]);
        $order_count = $stmt_check->fetch()['count'];
        
        if ($order_count > 0) {
            $error = 'Нельзя удалить магазин, к которому привязаны заказы';
        } else {
            $stmt = $pdo->prepare("DELETE FROM shops WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Магазин удален';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

// Получаем все магазины
$stmt = $pdo->query("SELECT * FROM shops ORDER BY name");
$shops = $stmt->fetchAll();

$page_title = 'Управление магазинами';
include __DIR__ . '/../includes/header.php';
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
                <li><a href="users.php" class="admin-nav-link">Пользователи</a></li>
                <li><a href="shops.php" class="admin-nav-link active">Магазины</a></li>
                <li><a href="<?php echo BASE_URL; ?>" class="admin-nav-link">На сайт</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Управление магазинами</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Форма добавления/редактирования -->
        <div class="admin-form-section">
            <h2><?php echo $edit_id ? 'Редактировать магазин' : 'Добавить магазин'; ?></h2>
            <form method="POST" class="admin-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Название *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($edit_shop['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_shop['phone'] ?? ''); ?>" 
                               placeholder="+7 (495) 123-45-67">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Адрес *</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($edit_shop['address'] ?? ''); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Широта (latitude) *</label>
                        <input type="number" name="latitude" step="0.00000001" 
                               value="<?php echo htmlspecialchars($edit_shop['latitude'] ?? ''); ?>" 
                               placeholder="55.7558" required>
                        <small style="color: #757575;">Например: 55.7558 (для Москвы)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Долгота (longitude) *</label>
                        <input type="number" name="longitude" step="0.00000001" 
                               value="<?php echo htmlspecialchars($edit_shop['longitude'] ?? ''); ?>" 
                               placeholder="37.6173" required>
                        <small style="color: #757575;">Например: 37.6173 (для Москвы)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Часы работы</label>
                    <input type="text" name="work_hours" value="<?php echo htmlspecialchars($edit_shop['work_hours'] ?? ''); ?>" 
                           placeholder="Пн-Вс: 10:00-22:00">
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="3" placeholder="Краткое описание магазина"><?php echo htmlspecialchars($edit_shop['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Как добраться</label>
                    <textarea name="how_to_get" rows="2" placeholder="Инструкции по проезду, рядом с метро и т.д."><?php echo htmlspecialchars($edit_shop['how_to_get'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_id ? 'Сохранить' : 'Добавить'; ?></button>
                    <?php if ($edit_id): ?>
                        <a href="shops.php" class="btn btn-secondary">Отмена</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Список магазинов -->
        <div class="admin-table-section">
            <h2>Список магазинов</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Адрес</th>
                            <th>Телефон</th>
                            <th>Часы работы</th>
                            <th>Координаты</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($shops)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">Магазины не добавлены</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($shops as $shop): ?>
                                <tr>
                                    <td><?php echo $shop['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($shop['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($shop['address']); ?></td>
                                    <td><?php echo htmlspecialchars($shop['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($shop['work_hours'] ?? '-'); ?></td>
                                    <td>
                                        <small>
                                            <?php echo htmlspecialchars($shop['latitude']); ?>, 
                                            <?php echo htmlspecialchars($shop['longitude']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="?edit=<?php echo $shop['id']; ?>" class="btn-edit">Редактировать</a>
                                        <a href="?delete=<?php echo $shop['id']; ?>" 
                                           onclick="return confirm('Удалить магазин?')" 
                                           class="btn-delete">Удалить</a>
                                        <a href="<?php echo BASE_URL; ?>shops.php" target="_blank" class="btn-view">На сайте</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

