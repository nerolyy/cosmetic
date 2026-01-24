<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$error = '';
$success = '';

// Обработка изменения статуса заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            $success = 'Статус заказа обновлен';
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    } else {
        $error = 'Неверный статус';
    }
}

// Обработка отмены заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    try {
        // Проверяем текущий статус заказа
        $stmt_check = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt_check->execute([$order_id]);
        $current_order = $stmt_check->fetch();
        
        if ($current_order) {
            if ($current_order['status'] === 'delivered') {
                $error = 'Нельзя отменить доставленный заказ';
            } elseif ($current_order['status'] === 'cancelled') {
                $error = 'Заказ уже отменен';
            } else {
                $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$order_id]);
                $success = 'Заказ отменен';
            }
        } else {
            $error = 'Заказ не найден';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
}

// Фильтры
$status_filter = $_GET['status'] ?? 'all';
$user_id_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Получаем заказы с фильтрами
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($user_id_filter) {
    $where_conditions[] = "o.user_id = ?";
    $params[] = $user_id_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$sql = "
    SELECT o.*, 
           u.name as user_name,
           u.email as user_email,
           s.name as shop_name,
           s.address as shop_address,
           COUNT(oi.id) as items_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN shops s ON o.shop_id = s.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    $where_clause
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Получаем детали заказа для просмотра
$order_detail = null;
$order_items = [];
if (isset($_GET['view'])) {
    $order_id = (int)$_GET['view'];
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
               s.name as shop_name, s.address as shop_address, s.phone as shop_phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN shops s ON o.shop_id = s.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order_detail = $stmt->fetch();
    
    if ($order_detail) {
        $stmt_items = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.image, b.name as brand_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order_id]);
        $order_items = $stmt_items->fetchAll();
    }
}

// Получаем всех пользователей для фильтра
$stmt_users = $pdo->query("SELECT id, name, email FROM users ORDER BY name");
$all_users = $stmt_users->fetchAll();

$page_title = 'Управление заказами';
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
                <li><a href="orders.php" class="admin-nav-link active">Заказы</a></li>
                <li><a href="users.php" class="admin-nav-link">Пользователи</a></li>
                <li><a href="shops.php" class="admin-nav-link">Магазины</a></li>
                <li><a href="<?php echo BASE_URL; ?>" class="admin-nav-link">На сайт</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Управление заказами</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($order_detail): ?>
            <!-- Детали заказа -->
            <div class="order-detail-admin">
                <a href="orders.php<?php echo $user_id_filter ? '?user_id=' . $user_id_filter : ''; ?><?php echo $status_filter !== 'all' ? ($user_id_filter ? '&' : '?') . 'status=' . $status_filter : ''; ?>" class="btn-back">← Назад к списку заказов</a>
                
                <div class="order-detail-card-admin">
                    <div class="order-detail-header-admin">
                        <h2>Заказ №<?php echo $order_detail['id']; ?></h2>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <form method="POST" style="display: inline-flex; gap: 10px; align-items: center;">
                                <input type="hidden" name="order_id" value="<?php echo $order_detail['id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="pending" <?php echo $order_detail['status'] === 'pending' ? 'selected' : ''; ?>>Ожидает обработки</option>
                                    <option value="processing" <?php echo $order_detail['status'] === 'processing' ? 'selected' : ''; ?>>В обработке</option>
                                    <option value="shipped" <?php echo $order_detail['status'] === 'shipped' ? 'selected' : ''; ?>>Отправлен</option>
                                    <option value="delivered" <?php echo $order_detail['status'] === 'delivered' ? 'selected' : ''; ?>>Доставлен</option>
                                    <option value="cancelled" <?php echo $order_detail['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменен</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-update-status">Обновить статус</button>
                            </form>
                            <?php if ($order_detail['status'] !== 'cancelled' && $order_detail['status'] !== 'delivered'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите отменить этот заказ?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order_detail['id']; ?>">
                                    <button type="submit" name="cancel_order" class="btn-cancel-order" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                                        Отменить заказ
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-detail-info-admin">
                        <div class="info-row">
                            <strong>Дата заказа:</strong>
                            <span><?php echo date('d.m.Y H:i', strtotime($order_detail['created_at'])); ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Пользователь:</strong>
                            <span>
                                <?php echo htmlspecialchars($order_detail['user_name']); ?> 
                                (<a href="?user_id=<?php echo $order_detail['user_id']; ?>"><?php echo htmlspecialchars($order_detail['user_email']); ?></a>)
                            </span>
                        </div>
                        <?php if (!empty($order_detail['recipient_name'])): ?>
                            <div class="info-row">
                                <strong>Получатель:</strong>
                                <span><?php echo htmlspecialchars($order_detail['recipient_name']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order_detail['recipient_phone'])): ?>
                            <div class="info-row">
                                <strong>Телефон получателя:</strong>
                                <span><?php echo htmlspecialchars($order_detail['recipient_phone']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order_detail['delivery_method'])): ?>
                            <div class="info-row">
                                <strong>Способ доставки:</strong>
                                <span><?php echo $order_detail['delivery_method'] === 'courier' ? 'Курьером' : 'Самовывоз'; ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order_detail['address'])): ?>
                            <div class="info-row">
                                <strong>Адрес доставки:</strong>
                                <span><?php echo htmlspecialchars($order_detail['address']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order_detail['shop_name'])): ?>
                            <div class="info-row">
                                <strong>Магазин самовывоза:</strong>
                                <span>
                                    <strong><?php echo htmlspecialchars($order_detail['shop_name']); ?></strong>
                                    <?php if (!empty($order_detail['shop_address'])): ?>
                                        <br><?php echo htmlspecialchars($order_detail['shop_address']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($order_detail['shop_phone'])): ?>
                                        <br>Телефон: <?php echo htmlspecialchars($order_detail['shop_phone']); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-items-admin">
                        <h3>Товары в заказе</h3>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th>Количество</th>
                                    <th>Цена за ед.</th>
                                    <th>Итого</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <?php if ($item['image']): ?>
                                                    <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                    <?php if ($item['brand_name']): ?>
                                                        <br><small style="color: #757575;"><?php echo htmlspecialchars($item['brand_name']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo number_format($item['price'], 0, ',', ' '); ?> Р</td>
                                        <td><strong><?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> Р</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right;"><strong>Итого:</strong></td>
                                    <td><strong style="font-size: 18px; color: var(--pink-bright);"><?php echo number_format($order_detail['total'], 0, ',', ' '); ?> Р</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Список заказов -->
            <div class="admin-filters">
                <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div class="filter-group">
                        <label>Статус:</label>
                        <select name="status" onchange="this.form.submit()">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Все</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Ожидают обработки</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>В обработке</option>
                            <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Отправлены</option>
                            <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Доставлены</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Отменены</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Пользователь:</label>
                        <select name="user_id" onchange="this.form.submit()">
                            <option value="">Все пользователи</option>
                            <?php foreach ($all_users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $user_id_filter == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($user_id_filter || $status_filter !== 'all'): ?>
                        <a href="orders.php" class="btn-clear-filters">Сбросить фильтры</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="admin-table-section">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дата</th>
                                <th>Пользователь</th>
                                <th>Получатель</th>
                                <th>Телефон</th>
                                <th>Доставка</th>
                                <th>Товаров</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 40px;">Заказы не найдены</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="?user_id=<?php echo $order['user_id']; ?>">
                                                <?php echo htmlspecialchars($order['user_name']); ?>
                                            </a>
                                            <br><small style="color: #757575;"><?php echo htmlspecialchars($order['user_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['recipient_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($order['recipient_phone'] ?? '-'); ?></td>
                                        <td>
                                            <?php 
                                            if ($order['delivery_method'] === 'courier') {
                                                echo 'Курьером';
                                                if ($order['address']) {
                                                    echo '<br><small style="color: #757575;">' . htmlspecialchars(mb_substr($order['address'], 0, 30)) . '...</small>';
                                                }
                                            } else {
                                                echo 'Самовывоз';
                                                if (!empty($order['shop_name'])) {
                                                    echo '<br><small style="color: #757575;">' . htmlspecialchars($order['shop_name']) . '</small>';
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $order['items_count']; ?></td>
                                        <td><strong><?php echo number_format($order['total'], 0, ',', ' '); ?> Р</strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo htmlspecialchars($order['status']); ?>">
                                                <?php
                                                $status_labels = [
                                                    'pending' => 'Ожидает',
                                                    'processing' => 'В обработке',
                                                    'shipped' => 'Отправлен',
                                                    'delivered' => 'Доставлен',
                                                    'cancelled' => 'Отменен'
                                                ];
                                                echo $status_labels[$order['status']] ?? $order['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <a href="?view=<?php echo $order['id']; ?><?php echo $user_id_filter ? '&user_id=' . $user_id_filter : ''; ?><?php echo $status_filter !== 'all' ? '&status=' . $status_filter : ''; ?>" class="btn-view">Просмотр</a>
                                                <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'delivered'): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Отменить заказ №<?php echo $order['id']; ?>?');">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" name="cancel_order" class="btn-cancel-order-small" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;" title="Отменить заказ">
                                                            Отменить
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

