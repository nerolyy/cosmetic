<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Получаем данные пользователя
$user = getCurrentUser();

// Получаем список магазинов для самовывоза
$stmt_shops = $pdo->query("SELECT * FROM shops ORDER BY name");
$shops = $stmt_shops->fetchAll();

// Получаем сохраненные адреса пользователя
$stmt_addresses = $pdo->prepare("
    SELECT * FROM user_addresses 
    WHERE user_id = ? AND delivery_address IS NOT NULL AND delivery_address != ''
    ORDER BY is_default DESC, created_at DESC
");
$stmt_addresses->execute([$_SESSION['user_id']]);
$user_addresses = $stmt_addresses->fetchAll();

// Получаем товары в корзине
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image, p.slug, b.name as brand_name
    FROM cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Вычисляем общую стоимость
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$page_title = 'Корзина';
include 'includes/header.php';
?>

<div class="cart-page">
    <div class="cart-top-bar"></div>
    
    <div class="container">
        <div class="cart-layout">
            <!-- Левая колонка: Шаги оформления -->
            <div class="cart-steps">
                <!-- Шаг 1: Адрес и способ доставки -->
                <div class="cart-step active" data-step="1">
                    <div class="step-header">
                        <h2 class="step-title">1/2 адрес и способ доставки</h2>
                        <button class="step-toggle" aria-label="Свернуть/развернуть">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 15l-6-6-6 6"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="step-content">
                        <div class="delivery-methods">
                            <label class="delivery-label">способ доставки</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="delivery" value="courier" checked>
                                    <span class="radio-label">курьер</span>
                                    <span class="radio-price">бесплатно</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="delivery" value="pickup">
                                    <span class="radio-label">самовывоз</span>
                                    <span class="radio-price">бесплатно</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Адрес для доставки курьером -->
                        <div class="delivery-address" id="delivery-address">
                            <label class="form-label" for="address">адрес доставки</label>
                            <?php if (!empty($user_addresses)): ?>
                                <select id="address-select" name="address-select" class="form-input" style="margin-bottom: 10px;">
                                    <option value="">Выберите адрес или введите новый</option>
                                    <?php foreach ($user_addresses as $addr): ?>
                                        <option value="<?php echo htmlspecialchars($addr['delivery_address']); ?>" 
                                                <?php echo $addr['is_default'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($addr['delivery_address']); ?>
                                            <?php echo $addr['is_default'] ? ' (по умолчанию)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" id="address" name="address" class="form-input" 
                                       placeholder="Или введите новый адрес: Улица, дом, квартира">
                            <?php else: ?>
                                <input type="text" id="address" name="address" class="form-input" 
                                       placeholder="Улица, дом, квартира">
                            <?php endif; ?>
                            <span class="error-message" id="address-error"></span>
                        </div>
                        
                        <!-- Выбор магазина для самовывоза -->
                        <div class="pickup-shop" id="pickup-shop" style="display: none;">
                            <label class="form-label" for="shop_id">выберите магазин для самовывоза</label>
                            <select id="shop_id" name="shop_id" class="form-input">
                                <option value="">Выберите магазин</option>
                                <?php foreach ($shops as $shop): ?>
                                    <option value="<?php echo $shop['id']; ?>">
                                        <?php echo htmlspecialchars($shop['name']); ?> - <?php echo htmlspecialchars($shop['address']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="error-message" id="shop-error"></span>
                        </div>
                    </div>
                </div>

                <!-- Шаг 2: Получатель -->
                <div class="cart-step active" data-step="2">
                    <div class="step-header">
                        <h2 class="step-title">2/2 получатель</h2>
                        <button class="step-toggle" aria-label="Свернуть/развернуть">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 15l-6-6-6 6"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="step-content">
                        <div class="form-group">
                            <label class="form-label" for="recipient-name">имя</label>
                            <input type="text" id="recipient-name" name="recipient_name" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            <span class="error-message" id="name-error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="recipient-phone">номер телефона</label>
                            <input type="tel" id="recipient-phone" name="recipient_phone" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   placeholder="+7 (999) 123-45-67" required>
                            <span class="error-message" id="phone-error"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Правая колонка: Итоговая сумма -->
            <div class="cart-summary">
                <h2 class="summary-title">сумма заказа</h2>

                <div class="cart-items-list">
                    <?php if (!empty($cart_items)): ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item-summary" data-product-id="<?php echo $item['product_id']; ?>" data-price="<?php echo $item['price']; ?>" data-quantity="<?php echo $item['quantity']; ?>">
                                <div class="cart-item-image">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <div class="cart-item-image-placeholder">Нет фото</div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <div class="item-quantity-controls">
                                        <button class="btn-quantity btn-quantity-minus" data-product-id="<?php echo $item['product_id']; ?>" aria-label="Уменьшить количество">−</button>
                                        <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="99" data-product-id="<?php echo $item['product_id']; ?>">
                                        <button class="btn-quantity btn-quantity-plus" data-product-id="<?php echo $item['product_id']; ?>" aria-label="Увеличить количество">+</button>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <span class="item-price" data-total="<?php echo $item['price'] * $item['quantity']; ?>"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> Р</span>
                                    <button class="btn-remove-item" data-product-id="<?php echo $item['product_id']; ?>" aria-label="Удалить товар из корзины">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-cart-message">Корзина пуста</p>
                    <?php endif; ?>
                </div>

                <!-- Промокод -->
                <div class="promo-code-section" style="margin-bottom: 20px; padding: 16px; background: var(--bg-light); border-radius: 8px;">
                    <label for="promo-code-input" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: var(--text-color);">Промокод</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="promo-code-input" placeholder="Введите промокод" 
                               style="flex: 1; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; transition: var(--transition);"
                               onkeypress="if(event.key === 'Enter') applyPromoCode()">
                        <button type="button" id="apply-promo-btn" onclick="applyPromoCode()" 
                                style="padding: 10px 20px; background: var(--pink-bright); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: var(--transition);">
                            Применить
                        </button>
                    </div>
                    <div id="promo-code-message" style="margin-top: 8px; font-size: 13px; min-height: 18px;"></div>
                    <div id="promo-code-info" style="display: none; margin-top: 12px; padding: 12px; background: rgba(76, 175, 80, 0.1); border-radius: 6px; font-size: 13px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="color: var(--text-color);">Промокод применен:</span>
                            <span id="promo-code-name" style="font-weight: 600; color: #2E7D32;"></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-color);">Скидка:</span>
                            <span id="promo-discount" style="font-weight: 600; color: #2E7D32;"></span>
                        </div>
                        <button type="button" onclick="removePromoCode()" 
                                style="margin-top: 8px; padding: 6px 12px; background: transparent; border: 1px solid #2E7D32; color: #2E7D32; border-radius: 6px; font-size: 12px; cursor: pointer;">
                            Удалить промокод
                        </button>
                    </div>
                </div>

                <div class="price-breakdown">
                    <div class="price-row">
                        <span class="price-label">стоимость продуктов</span>
                        <span class="price-dots"></span>
                        <span class="price-value" id="cart-total"><?php echo number_format($total, 0, ',', ' '); ?> Р</span>
                    </div>
                    <div class="price-row" id="promo-discount-row" style="display: none;">
                        <span class="price-label" style="color: #2E7D32;">скидка по промокоду</span>
                        <span class="price-dots"></span>
                        <span class="price-value" id="promo-discount-amount" style="color: #2E7D32;">0 Р</span>
                    </div>
                    <div class="price-row total">
                        <span class="price-label">итого</span>
                        <span class="price-dots"></span>
                        <span class="price-value" id="cart-total-final"><?php echo number_format($total, 0, ',', ' '); ?> Р</span>
                    </div>
                </div>

                <div class="delivery-date-info" id="delivery-date-info" style="margin-top: 16px; padding: 12px; background: var(--pink-light); border-radius: 8px; font-size: 14px; color: var(--text-color);">
                    <strong>Примерная дата получения:</strong> <span id="delivery-date-text">—</span>
                </div>

                <button class="btn-order" id="order-btn">
                    заказать
                </button>
                <div class="order-message" id="order-message"></div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

