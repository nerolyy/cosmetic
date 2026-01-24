<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$section = $_GET['section'] ?? 'main';

// Если перешли на страницу редактирования и есть флаг обновления, принудительно очищаем кеш
if ($section === 'edit' && isset($_SESSION['user_data_updated'])) {
    clearUserCache();
}

$user = getCurrentUser();

// Получаем избранные товары
$stmt_favorites = $pdo->prepare("
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM favorites f
    JOIN products p ON f.product_id = p.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
    LIMIT 6
");
$stmt_favorites->execute([$_SESSION['user_id']]);
$favorites = $stmt_favorites->fetchAll();

// Получаем заказы пользователя (только незавершенные для главной страницы)
$stmt_orders = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as items_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ? AND o.status != 'delivered'
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$stmt_orders->execute([$_SESSION['user_id']]);
$orders = $stmt_orders->fetchAll();

// Получаем все заказы для страницы "Мои заказы"
$stmt_all_orders = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as items_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt_all_orders->execute([$_SESSION['user_id']]);
$all_orders = $stmt_all_orders->fetchAll();

// Получаем детали заказа с товарами
function getOrderDetails($pdo, $order_id) {
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image, b.name as brand_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

// Получаем все магазины для выбора
$stmt_shops = $pdo->query("SELECT * FROM shops ORDER BY name");
$shops = $stmt_shops->fetchAll();

// Получаем адреса пользователя
$stmt_addresses = $pdo->prepare("
    SELECT ua.*, s.name as shop_name, s.address as shop_address
    FROM user_addresses ua
    LEFT JOIN shops s ON ua.shop_id = s.id
    WHERE ua.user_id = ?
    ORDER BY ua.is_default DESC, ua.created_at DESC
");
$stmt_addresses->execute([$_SESSION['user_id']]);
$user_addresses = $stmt_addresses->fetchAll();

$page_title = 'Личный кабинет';
include __DIR__ . '/../includes/header.php';
?>

<div class="profile-container">
    <div class="profile-sidebar">
        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        <nav class="profile-nav">
            <ul>
                <li><a href="?section=orders" class="<?php echo $section === 'orders' ? 'active' : ''; ?>">Мои заказы</a></li>
                <li><a href="?section=addresses" class="<?php echo $section === 'addresses' ? 'active' : ''; ?>">Мои адреса</a></li>
                <li><a href="?section=edit" class="<?php echo $section === 'edit' ? 'active' : ''; ?>">Редактировать данные</a></li>
                <li><a href="<?php echo BASE_URL; ?>logout.php">Выйти</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="profile-content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if ($section === 'main'): ?>
            <h1>Добро пожаловать, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            
            <!-- Ваши заказы -->
            <section class="profile-section">
                <h2>Ваши заказы</h2>
                <?php if (!empty($orders)): ?>
                    <div class="orders-list orders-list-scrollable">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <span class="order-number">Заказ №<?php echo $order['id']; ?></span>
                                        <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-status order-status-<?php echo htmlspecialchars($order['status']); ?>">
                                        <?php
                                        $status_labels = [
                                            'pending' => 'Ожидает обработки',
                                            'processing' => 'В обработке',
                                            'shipped' => 'Отправлен',
                                            'delivered' => 'Доставлен',
                                            'cancelled' => 'Отменен'
                                        ];
                                        echo $status_labels[$order['status']] ?? $order['status'];
                                        ?>
                                    </div>
                                </div>
                                <div class="order-details">
                                    <p>Товаров: <?php echo $order['items_count']; ?></p>
                                    <p class="order-total">Сумма: <?php echo number_format($order['total'], 0, ',', ' '); ?> Р</p>
                                    <?php 
                                    // Определяем дату доставки
                                    $delivery_date = null;
                                    if (!empty($order['delivery_date'])) {
                                        $delivery_date = $order['delivery_date'];
                                    } else {
                                        // Если даты нет в БД, вычисляем примерную дату на основе статуса
                                        $created_date = new DateTime($order['created_at']);
                                        if ($order['status'] === 'pending' || $order['status'] === 'processing') {
                                            // Для ожидающих обработки или в обработке - примерно через 2-3 дня
                                            $created_date->modify('+2 days');
                                        } elseif ($order['status'] === 'shipped') {
                                            // Для отправленных - примерно через 1 день
                                            $created_date->modify('+1 day');
                                        }
                                        $delivery_date = $created_date->format('Y-m-d');
                                    }
                                    if ($delivery_date && $order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): 
                                    ?>
                                        <p class="order-delivery-date">Примерная дата доставки: <?php echo date('d.m.Y', strtotime($delivery_date)); ?></p>
                                    <?php endif; ?>
                                </div>
                                <a href="?section=orders&order_id=<?php echo $order['id']; ?>" class="btn-view-order">Подробнее</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="?section=orders" class="btn-view-all">Посмотреть все заказы</a>
                <?php else: ?>
                    <p>У вас пока нет заказов</p>
                <?php endif; ?>
            </section>
            
            <!-- Избранное -->
            <section class="profile-section">
                <h2>Избранное</h2>
                <?php if (!empty($favorites)): ?>
                    <div class="products-grid">
                        <?php foreach ($favorites as $product): ?>
                            <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                                <button class="product-favorite active" data-product-id="<?php echo $product['id']; ?>" aria-label="Удалить из избранного" style="position: absolute; top: 8px; right: 8px; z-index: 3;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>
                                <a href="<?php echo BASE_URL; ?>product.php?product=<?php echo htmlspecialchars($product['slug']); ?>" class="product-link">
                                    <div class="product-image">
                                        <?php if ($product['image']): ?>
                                            <img src="<?php echo UPLOADS_URL . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <?php else: ?>
                                            <div class="product-image-placeholder">Нет фото</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p class="product-brand"><?php echo htmlspecialchars($product['brand_name'] ?? ''); ?></p>
                                        <p class="product-price"><?php echo number_format($product['price'], 0, ',', ' '); ?> Р</p>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>У вас пока нет избранных товаров</p>
                <?php endif; ?>
            </section>
            
        <?php elseif ($section === 'orders'): ?>
            <h1>Мои заказы</h1>
            
            <?php if (!empty($_GET['order_id'])): ?>
                <?php
                $order_id = (int)$_GET['order_id'];
                $stmt_order = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
                $stmt_order->execute([$order_id, $_SESSION['user_id']]);
                $order_detail = $stmt_order->fetch();
                
                if ($order_detail):
                    $order_items = getOrderDetails($pdo, $order_id);
                ?>
                    <div class="order-detail-page">
                        <a href="?section=orders" class="btn-back">← Назад к заказам</a>
                        
                        <div class="order-detail-card">
                            <div class="order-detail-header">
                                <h2>Заказ №<?php echo $order_detail['id']; ?></h2>
                                <div class="order-status order-status-<?php echo htmlspecialchars($order_detail['status']); ?>">
                                    <?php
                                    $status_labels = [
                                        'pending' => 'Ожидает обработки',
                                        'processing' => 'В обработке',
                                        'shipped' => 'Отправлен',
                                        'delivered' => 'Доставлен',
                                        'cancelled' => 'Отменен'
                                    ];
                                    echo $status_labels[$order_detail['status']] ?? $order_detail['status'];
                                    ?>
                                </div>
                            </div>
                            
                            <div class="order-detail-info">
                                <p><strong>Дата заказа:</strong> <?php echo date('d.m.Y H:i', strtotime($order_detail['created_at'])); ?></p>
                                <?php if (!empty($order_detail['delivery_date']) && $order_detail['status'] !== 'delivered'): ?>
                                    <p><strong>Ожидаемая дата доставки:</strong> <?php echo date('d.m.Y', strtotime($order_detail['delivery_date'])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($order_detail['recipient_name'])): ?>
                                    <p><strong>Получатель:</strong> <?php echo htmlspecialchars($order_detail['recipient_name']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($order_detail['recipient_phone'])): ?>
                                    <p><strong>Телефон:</strong> <?php echo htmlspecialchars($order_detail['recipient_phone']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($order_detail['delivery_method'])): ?>
                                    <p><strong>Способ доставки:</strong> 
                                        <?php echo $order_detail['delivery_method'] === 'courier' ? 'Курьером' : 'Самовывоз'; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($order_detail['address'])): ?>
                                    <p><strong>Адрес доставки:</strong> <?php echo htmlspecialchars($order_detail['address']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-items">
                                <h3>Товары в заказе</h3>
                                <div class="order-items-list">
                                    <?php foreach ($order_items as $item): ?>
                                        <div class="order-item">
                                            <div class="order-item-image">
                                                <?php if ($item['image']): ?>
                                                    <img src="<?php echo UPLOADS_URL . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                                <?php else: ?>
                                                    <div class="product-image-placeholder">Нет фото</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="order-item-info">
                                                <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                                <?php if ($item['brand_name']): ?>
                                                    <p class="order-item-brand"><?php echo htmlspecialchars($item['brand_name']); ?></p>
                                                <?php endif; ?>
                                                <p>Количество: <?php echo $item['quantity']; ?></p>
                                                <p class="order-item-price"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> Р</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="order-total-section">
                                <p class="order-total-amount">Итого: <?php echo number_format($order_detail['total'], 0, ',', ' '); ?> Р</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Заказ не найден</p>
                    <a href="?section=orders" class="btn-back">← Назад к заказам</a>
                <?php endif; ?>
            <?php else: ?>
                <div class="orders-filter">
                    <button class="filter-btn active" data-status="all">Все</button>
                    <button class="filter-btn" data-status="pending">Ожидают обработки</button>
                    <button class="filter-btn" data-status="processing">В обработке</button>
                    <button class="filter-btn" data-status="shipped">Отправлены</button>
                    <button class="filter-btn" data-status="delivered">Доставлены</button>
                    <button class="filter-btn" data-status="cancelled">Отменены</button>
                </div>
                
                <?php if (!empty($all_orders)): ?>
                    <div class="orders-list">
                        <?php foreach ($all_orders as $order): ?>
                            <div class="order-card" data-status="<?php echo htmlspecialchars($order['status']); ?>">
                                <div class="order-header">
                                    <div class="order-info">
                                        <span class="order-number">Заказ №<?php echo $order['id']; ?></span>
                                        <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-status order-status-<?php echo htmlspecialchars($order['status']); ?>">
                                        <?php
                                        $status_labels = [
                                            'pending' => 'Ожидает обработки',
                                            'processing' => 'В обработке',
                                            'shipped' => 'Отправлен',
                                            'delivered' => 'Доставлен',
                                            'cancelled' => 'Отменен'
                                        ];
                                        echo $status_labels[$order['status']] ?? $order['status'];
                                        ?>
                                    </div>
                                </div>
                                <div class="order-details">
                                    <p>Товаров: <?php echo $order['items_count']; ?></p>
                                    <p class="order-total">Сумма: <?php echo number_format($order['total'], 0, ',', ' '); ?> Р</p>
                                    <?php if (!empty($order['delivery_date']) && $order['status'] !== 'delivered'): ?>
                                        <p class="order-delivery-date">Ожидаемая дата доставки: <?php echo date('d.m.Y', strtotime($order['delivery_date'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                <a href="?section=orders&order_id=<?php echo $order['id']; ?>" class="btn-view-order">Подробнее</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>У вас пока нет заказов</p>
                <?php endif; ?>
            <?php endif; ?>
            
        <?php elseif ($section === 'addresses'): ?>
            <h1>Мои адреса</h1>
            
            <div class="addresses-section">
                <div class="add-address-form">
                    <h2>Добавить адрес</h2>
                    <form id="address-form" method="POST" action="<?php echo BASE_URL; ?>api/save_address.php">
                        <div class="form-group">
                            <label class="form-label">Выберите любимый магазин (для самовывоза)</label>
                            <select name="shop_id" id="shop_id" class="form-input">
                                <option value="">Не выбран</option>
                                <?php foreach ($shops as $shop): ?>
                                    <option value="<?php echo $shop['id']; ?>"><?php echo htmlspecialchars($shop['name']); ?> - <?php echo htmlspecialchars($shop['address']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Адрес для доставки курьером</label>
                            <textarea name="delivery_address" id="delivery_address" class="form-input" rows="3" placeholder="Введите полный адрес доставки"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="is_default" value="1">
                                <span>Сделать адресом по умолчанию</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-save-address">Сохранить адрес</button>
                    </form>
                </div>
                
                <div class="addresses-list">
                    <h2>Сохраненные адреса</h2>
                    <?php if (!empty($user_addresses)): ?>
                        <?php foreach ($user_addresses as $address): ?>
                            <div class="address-card">
                                <?php if ($address['shop_id']): ?>
                                    <div class="address-type">Самовывоз</div>
                                    <h3><?php echo htmlspecialchars($address['shop_name']); ?></h3>
                                    <p><?php echo htmlspecialchars($address['shop_address']); ?></p>
                                <?php else: ?>
                                    <div class="address-type">Доставка курьером</div>
                                    <p><?php echo nl2br(htmlspecialchars($address['delivery_address'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($address['is_default']): ?>
                                    <span class="address-default-badge">По умолчанию</span>
                                <?php endif; ?>
                                
                                <div class="address-actions">
                                    <form method="POST" action="<?php echo BASE_URL; ?>api/delete_address.php" style="display: inline;">
                                        <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                        <button type="submit" class="btn-delete-address" onclick="return confirm('Удалить этот адрес?');">Удалить</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>У вас пока нет сохраненных адресов</p>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php elseif ($section === 'edit'): ?>
            <h1>Редактировать данные</h1>
            
            <div class="edit-profile-section">
                <form method="POST" action="<?php echo BASE_URL; ?>api/update_profile.php" class="profile-edit-form">
                    <div class="form-group">
                        <label for="name" class="form-label">Имя <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="tel" id="phone" name="phone" class="form-input" value="<?php 
                            if (!empty($user['phone'])) {
                                // Убираем форматирование (скобки, дефисы, пробелы, +)
                                $phone = preg_replace('/[^0-9]/', '', $user['phone']);
                                // Если начинается с 7, убираем его для отображения
                                if (strlen($phone) === 11 && $phone[0] === '7') {
                                    // Форматируем для отображения: +7 (999) 123-45-67
                                    echo '+7 (' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9);
                                } elseif (strlen($phone) === 11 && $phone[0] === '8') {
                                    // Если начинается с 8, заменяем на 7
                                    echo '+7 (' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9);
                                } elseif (strlen($phone) === 10) {
                                    // Если 10 цифр, добавляем 7
                                    echo '+7 (' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6, 2) . '-' . substr($phone, 8);
                                } else {
                                    // Просто отображаем как есть
                                    echo htmlspecialchars($user['phone']);
                                }
                            }
                        ?>" placeholder="+7 (999) 123-45-67">
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password" class="form-label">Текущий пароль</label>
                        <input type="password" id="current_password" name="current_password" class="form-input" placeholder="Укажите только если хотите изменить пароль">
                        <small class="form-hint">Оставьте пустым, если не хотите менять пароль</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Новый пароль</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Укажите новый пароль">
                        <small class="form-hint">Оставьте пустым, если не хотите менять пароль</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Подтвердите новый пароль</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Повторите новый пароль">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save-profile">Сохранить изменения</button>
                        <a href="?section=main" class="btn-cancel-profile">Отмена</a>
                    </div>
                </form>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<script>
// Фильтрация заказов по статусу
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const orderCards = document.querySelectorAll('.order-card');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            
            // Обновляем активную кнопку
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Фильтруем заказы
            orderCards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // Валидация формы редактирования профиля
    const profileForm = document.querySelector('.profile-edit-form');
    if (profileForm) {
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirm');
        const currentPasswordInput = document.getElementById('current_password');
        const phoneInput = document.getElementById('phone');
        
        // Форматирование телефона
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                // Оставляем только цифры
                let digits = this.value.replace(/[^0-9]/g, '');
                
                // Ограничиваем длину до 11 цифр
                if (digits.length > 11) {
                    digits = digits.substring(0, 11);
                }
                
                // Сохраняем только цифры (без форматирования во время ввода)
                this.value = digits;
            });

            // Форматирование телефона при потере фокуса
            phoneInput.addEventListener('blur', function() {
                let digits = this.value.replace(/[^0-9]/g, '');
                if (digits.length > 0) {
                    // Если начинается с 8, заменяем на 7
                    if (digits.startsWith('8')) {
                        digits = '7' + digits.substring(1);
                    }
                    // Если не начинается с 7 и есть 10 цифр, добавляем 7
                    if (!digits.startsWith('7') && digits.length === 10) {
                        digits = '7' + digits;
                    }
                    // Если меньше 11 цифр и не начинается с 7, добавляем 7
                    if (digits.length < 11 && !digits.startsWith('7')) {
                        digits = '7' + digits;
                    }
                    // Ограничиваем до 11 цифр
                    if (digits.length > 11) {
                        digits = digits.substring(0, 11);
                    }
                    
                    // Форматируем: +7 (999) 123-45-67 если 11 цифр
                    if (digits.length === 11 && digits.startsWith('7')) {
                        const formatted = `+7 (${digits.substring(1, 4)}) ${digits.substring(4, 7)}-${digits.substring(7, 9)}-${digits.substring(9)}`;
                        this.value = formatted;
                    } else {
                        // Просто оставляем цифры
                        this.value = digits;
                    }
                }
            });
        }
        
        profileForm.addEventListener('submit', function(e) {
            // Если указан новый пароль, проверяем подтверждение
            if (passwordInput && passwordInput.value) {
                if (passwordInput.value !== passwordConfirmInput.value) {
                    e.preventDefault();
                    alert('Новый пароль и подтверждение не совпадают');
                    passwordConfirmInput.focus();
                    return false;
                }
                
                if (passwordInput.value.length < 6) {
                    e.preventDefault();
                    alert('Пароль должен содержать минимум 6 символов');
                    passwordInput.focus();
                    return false;
                }
                
                // Если указан новый пароль, текущий пароль обязателен
                if (!currentPasswordInput.value) {
                    e.preventDefault();
                    alert('Для изменения пароля укажите текущий пароль');
                    currentPasswordInput.focus();
                    return false;
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
