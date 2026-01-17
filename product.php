<?php
require_once 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: catalog.php');
    exit;
}

// Получаем информацию о товаре
$stmt = $pdo->prepare("
    SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: catalog.php');
    exit;
}

// Проверяем, в избранном ли товар и в корзине ли
$is_favorite = false;
$is_in_cart = false;

if (isLoggedIn()) {
    $stmt_fav = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt_fav->execute([$_SESSION['user_id'], $product_id]);
    $is_favorite = $stmt_fav->fetchColumn() > 0;
    
    $stmt_cart = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt_cart->execute([$_SESSION['user_id'], $product_id]);
    $is_in_cart = $stmt_cart->fetchColumn() > 0;
}

$page_title = htmlspecialchars($product['name']);
include 'includes/header.php';
?>

<div class="product-detail-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <nav class="breadcrumbs">
            <a href="index.php">Главная</a>
            <span>/</span>
            <a href="catalog.php">Каталог</a>
            <?php if ($product['category_slug']): ?>
                <span>/</span>
                <a href="catalog.php?category=<?php echo htmlspecialchars($product['category_slug']); ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
            <?php endif; ?>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <div class="product-detail">
            <!-- Левая часть: изображение -->
            <div class="product-detail-image">
                <?php if ($product['image']): ?>
                    <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         id="product-main-image">
                <?php else: ?>
                    <div class="product-image-placeholder-large">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <p>Нет фото</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Правая часть: информация -->
            <div class="product-detail-info">
                <div class="product-detail-header">
                    <p class="product-category-link">
                        <?php if ($product['category_slug']): ?>
                            <a href="catalog.php?category=<?php echo htmlspecialchars($product['category_slug']); ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        <?php else: ?>
                            Косметика
                        <?php endif; ?>
                    </p>
                    <h1 class="product-detail-name"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <?php if ($product['brand_name']): ?>
                        <p class="product-detail-brand">
                            <span>Бренд:</span>
                            <a href="brands.php?brand=<?php echo htmlspecialchars($product['brand_id'] ?? ''); ?>">
                                <?php echo htmlspecialchars($product['brand_name']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="product-detail-badges">
                    <?php if ($product['discount'] > 0): ?>
                        <span class="product-badge badge-discount">Скидка <?php echo $product['discount']; ?>%</span>
                    <?php endif; ?>
                    <?php if ($product['is_new']): ?>
                        <span class="product-badge badge-new">NEW</span>
                    <?php endif; ?>
                    <?php if ($product['is_featured']): ?>
                        <span class="product-badge badge-hit">HIT</span>
                    <?php endif; ?>
                </div>

                <div class="product-detail-price-section">
                    <div class="product-price-large">
                        <?php if ($product['old_price']): ?>
                            <span class="price-old"><?php echo number_format($product['old_price'], 0, ',', ' '); ?> Р</span>
                        <?php endif; ?>
                        <span class="price-current"><?php echo number_format($product['price'], 0, ',', ' '); ?> Р</span>
                    </div>
                    <div class="product-stock-status">
                        <span class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php if ($product['stock'] > 0): ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                В наличии (<?php echo $product['stock']; ?> шт.)
                            <?php else: ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                Нет в наличии
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($product['description'])): ?>
                    <div class="product-detail-description">
                        <h3>Описание товара</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="product-detail-actions">
                    <?php if (isLoggedIn()): ?>
                        <button class="btn btn-primary product-cart-btn <?php echo $is_in_cart ? 'in-cart' : ''; ?>" 
                                data-product-id="<?php echo $product['id']; ?>"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            <span><?php echo $is_in_cart ? 'В корзине' : 'Добавить в корзину'; ?></span>
                        </button>
                        <button class="btn btn-secondary product-favorite <?php echo $is_favorite ? 'active' : ''; ?>" 
                                data-product-id="<?php echo $product['id']; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $is_favorite ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <span><?php echo $is_favorite ? 'В избранном' : 'В избранное'; ?></span>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">
                            <span>Войдите, чтобы добавить в корзину</span>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="product-detail-specs">
                    <div class="spec-item">
                        <span class="spec-label">Артикул:</span>
                        <span class="spec-value"><?php echo $product['id']; ?></span>
                    </div>
                    <?php if ($product['category_name']): ?>
                        <div class="spec-item">
                            <span class="spec-label">Категория:</span>
                            <span class="spec-value">
                                <a href="catalog.php?category=<?php echo htmlspecialchars($product['category_slug'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </a>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if ($product['brand_name']): ?>
                        <div class="spec-item">
                            <span class="spec-label">Бренд:</span>
                            <span class="spec-value"><?php echo htmlspecialchars($product['brand_name']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="spec-item">
                        <span class="spec-label">Наличие:</span>
                        <span class="spec-value <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php echo $product['stock'] > 0 ? $product['stock'] . ' шт.' : 'Нет в наличии'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="<?php echo BASE_URL; ?>js/cart.js?v=<?php echo time(); ?>" defer></script>
<script src="<?php echo BASE_URL; ?>js/favorites.js?v=<?php echo time(); ?>" defer></script>

