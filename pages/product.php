<?php
require_once __DIR__ . '/../config/config.php';

// Получаем slug товара из URL
$product_slug = $_GET['product'] ?? null;

if (!$product_slug) {
    header('Location: ' . BASE_URL . 'catalog.php');
    exit;
}

// Получаем информацию о товаре
$stmt = $pdo->prepare("
    SELECT p.*, 
           b.name as brand_name, 
           b.slug as brand_slug,
           c.name as category_name, 
           c.slug as category_slug
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ?
    LIMIT 1
");
$stmt->execute([$product_slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $page_title = 'Товар не найден';
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="container" style="padding: 60px 20px; text-align: center;">
        <h1>Товар не найден</h1>
        <p>Запрашиваемый товар не существует или был удален.</p>
        <a href="<?php echo BASE_URL; ?>catalog.php" class="btn btn-primary" style="margin-top: 20px;">Вернуться в каталог</a>
    </div>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Проверяем, в избранном ли товар
$is_favorite = false;
$in_cart = false;
if (isLoggedIn()) {
    $stmt_fav = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt_fav->execute([$_SESSION['user_id'], $product['id']]);
    $is_favorite = $stmt_fav->fetch() !== false;
    
    $stmt_cart = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt_cart->execute([$_SESSION['user_id'], $product['id']]);
    $in_cart = $stmt_cart->fetch() !== false;
}

// Получаем похожие товары (из той же категории или бренда)
$stmt_related = $pdo->prepare("
    SELECT p.*, b.name as brand_name
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE p.id != ? 
    AND (p.category_id = ? OR p.brand_id = ?)
    ORDER BY RAND()
    LIMIT 4
");
$stmt_related->execute([$product['id'], $product['category_id'], $product['brand_id']]);
$related_products = $stmt_related->fetchAll();

$page_title = $product['name'];
include __DIR__ . '/../includes/header.php';
?>

<div class="product-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <nav class="breadcrumbs">
            <a href="<?php echo BASE_URL; ?>">Главная</a>
            <span>›</span>
            <?php if ($product['category_slug']): ?>
                <a href="<?php echo BASE_URL; ?>catalog.php?category=<?php echo htmlspecialchars($product['category_slug']); ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
                <span>›</span>
            <?php endif; ?>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <div class="product-detail">
            <div class="product-detail-left">
                <!-- Изображение товара -->
                <div class="product-image-large">
                    <?php if ($product['image']): ?>
                        <img src="<?php echo UPLOADS_URL . htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <div class="product-image-placeholder">Нет фото</div>
                    <?php endif; ?>
                    
                    <?php if ($product['is_new']): ?>
                        <span class="product-badge product-badge-new">Новинка</span>
                    <?php endif; ?>
                    <?php if ($product['discount'] > 0): ?>
                        <span class="product-badge product-badge-discount">-<?php echo $product['discount']; ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Описание под изображением -->
                <?php if ($product['description']): ?>
                    <div class="product-description">
                        <h3 class="product-description-title">Описание</h3>
                        <div class="product-description-content">
                            <div class="product-description-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
                        </div>
                        <button class="product-description-toggle" aria-label="Развернуть описание">
                            <span class="toggle-text">Развернуть</span>
                            <svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-detail-right">
                <!-- Название и бренд -->
                <div class="product-header">
                    <?php if ($product['brand_name']): ?>
                        <a href="<?php echo BASE_URL; ?>catalog.php?brand=<?php echo htmlspecialchars($product['brand_slug']); ?>" 
                           class="product-brand-link">
                            <?php echo htmlspecialchars($product['brand_name']); ?>
                        </a>
                    <?php endif; ?>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                </div>

                <!-- Цена -->
                <div class="product-price-section">
                    <div class="product-price-main">
                        <?php if ($product['old_price']): ?>
                            <span class="product-price-old">
                                <?php echo number_format($product['old_price'], 0, ',', ' '); ?> Р
                            </span>
                        <?php endif; ?>
                        <span class="product-price-current">
                            <?php echo number_format($product['price'], 0, ',', ' '); ?> Р
                        </span>
                    </div>
                    <?php if ($product['discount'] > 0): ?>
                        <div class="product-discount">
                            Экономия <?php 
                                $savings = $product['old_price'] - $product['price'];
                                echo number_format($savings, 0, ',', ' ');
                            ?> Р
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Наличие -->
                <div class="product-stock">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="stock-available">В наличии (<?php echo $product['stock']; ?> шт.)</span>
                    <?php else: ?>
                        <span class="stock-unavailable">Нет в наличии</span>
                    <?php endif; ?>
                </div>

                <!-- Действия -->
                <div class="product-actions">
                    <?php if (isLoggedIn()): ?>
                        <button class="product-favorite-btn <?php echo $is_favorite ? 'active' : ''; ?>" 
                                data-product-id="<?php echo $product['id']; ?>"
                                aria-label="<?php echo $is_favorite ? 'Удалить из избранного' : 'Добавить в избранное'; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $is_favorite ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <span><?php echo $is_favorite ? 'В избранном' : 'В избранное'; ?></span>
                        </button>
                        <button class="product-cart-btn-large <?php echo $in_cart ? 'in-cart' : ''; ?>" 
                                data-product-id="<?php echo $product['id']; ?>"
                                aria-label="<?php echo $in_cart ? 'В корзине' : 'Добавить в корзину'; ?>"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            <span><?php echo $in_cart ? 'В корзине' : 'Добавить в корзину'; ?></span>
                        </button>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>login.php" class="product-cart-btn-large">
                            <span>Войти для покупки</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Похожие товары -->
        <?php if (!empty($related_products)): ?>
            <section class="related-products">
                <h2>Похожие товары</h2>
                <div class="products-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="<?php echo BASE_URL; ?>product.php?product=<?php echo htmlspecialchars($related['slug']); ?>">
                                    <?php if ($related['image']): ?>
                                        <img src="<?php echo UPLOADS_URL . htmlspecialchars($related['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($related['name']); ?>">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">Нет фото</div>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="product-info">
                                <a href="<?php echo BASE_URL; ?>product.php?product=<?php echo htmlspecialchars($related['slug']); ?>">
                                    <h3 class="product-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                                </a>
                                <p class="product-brand"><?php echo htmlspecialchars($related['brand_name'] ?? ''); ?></p>
                                <p class="product-price"><?php echo number_format($related['price'], 0, ',', ' '); ?> Р</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

