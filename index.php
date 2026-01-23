<?php
require_once 'config.php';

// Получаем популярные товары (хиты)
$stmt_hits = $pdo->prepare("
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_featured = 1 
    ORDER BY p.created_at DESC 
    LIMIT 4
");
$stmt_hits->execute();
$hits = $stmt_hits->fetchAll();

// Получаем новинки
$stmt_new = $pdo->prepare("
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_new = 1 
    ORDER BY p.created_at DESC 
    LIMIT 4
");
$stmt_new->execute();
$new_products = $stmt_new->fetchAll();

// Получаем избранные товары пользователя
$favorite_product_ids = [];
$cart_product_ids = [];
if (isLoggedIn()) {
    $stmt_fav = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt_fav->execute([$_SESSION['user_id']]);
    $favorite_product_ids = array_column($stmt_fav->fetchAll(), 'product_id');
    
    $stmt_cart = $pdo->prepare("SELECT product_id FROM cart WHERE user_id = ?");
    $stmt_cart->execute([$_SESSION['user_id']]);
    $cart_product_ids = array_column($stmt_cart->fetchAll(), 'product_id');
}

$page_title = 'Главная';
include 'includes/header.php';
?>

<!-- Объединенный слайдер: скидки и промокоды -->
<section class="unified-slider-section">
    <div class="unified-slider-container">
        <div class="unified-slider-wrapper">
            <!-- Слайд 1: Скидки до -50% (Hero) -->
            <div class="unified-slide unified-slide-active" data-slide="0">
                <div class="hero-background">
                    <div class="hero-particles">
                        <?php 
                        for($i = 0; $i < 60; $i++): 
                            $left = rand(0, 100);
                            $delay = rand(0, 20);
                            $duration = 15 + rand(0, 15);
                            $size = 30 + rand(0, 100);
                            $rotation = rand(0, 360);
                        ?>
                            <span class="hero-particle" style="left: <?php echo $left; ?>%; animation-delay: <?php echo $delay; ?>s; animation-duration: <?php echo $duration; ?>s; font-size: <?php echo $size; ?>px; --rotation: <?php echo $rotation; ?>deg;">%</span>
                        <?php endfor; ?>
                    </div>
                    <div class="hero-gradient-overlay"></div>
                    <div class="hero-shapes">
                        <div class="shape shape-1"></div>
                        <div class="shape shape-2"></div>
                        <div class="shape shape-3"></div>
                    </div>
                </div>
                <div class="container">
                    <div class="hero-content">
                        <h1 class="hero-title fade-in-up" data-delay="0.2">
                            <span class="title-line-1">скидки до</span>
                            <span class="title-line-2">-50%</span>
                        </h1>
                        <p class="hero-subtitle fade-in-up" data-delay="0.3">любимое, новое и нужное</p>
                        <p class="hero-description fade-in-up" data-delay="0.4">Откройте для себя мир премиальной косметики с эксклюзивными предложениями</p>
                        <div class="hero-actions fade-in-up" data-delay="0.5">
                            <a href="promotions.php" class="btn btn-hero">
                                <span>ПЕРЕЙТИ К АКЦИИ</span>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            <a href="catalog.php" class="btn btn-hero-secondary">
                                <span>Каталог товаров</span>
                            </a>
                        </div>
                        <div class="hero-stats fade-in-up" data-delay="0.6">
                            <div class="stat-item">
                                <div class="stat-number">1000+</div>
                                <div class="stat-label">Товаров</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">50+</div>
                                <div class="stat-label">Брендов</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">24/7</div>
                                <div class="stat-label">Поддержка</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Слайд 2: Промокод на первый заказ -->
            <div class="unified-slide" data-slide="1">
                <div class="promo-slide-background">
                    <div class="promo-shapes">
                        <div class="promo-shape promo-shape-1"></div>
                        <div class="promo-shape promo-shape-2"></div>
                        <div class="promo-shape promo-shape-3"></div>
                    </div>
                </div>
                <div class="container">
                    <div class="promo-slide-content">
                        <div class="promo-main-content">
                            <h2 class="promo-title">Промокод на первый заказ</h2>
                            <div class="promo-code-wrapper">
                                <div class="promo-code-label">Используйте промокод:</div>
                                <div class="promo-code" id="promo-code">FIRST10</div>
                                <div class="promo-code-description">Скидка 10% на первый заказ</div>
                            </div>
                            <a href="catalog.php" class="btn-promo-catalog">
                                <span>ИССЛЕДУЙТЕ КАТАЛОГ</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Слайд 3: Новинки -->
            <div class="unified-slide" data-slide="2">
                <div class="promo-slide-background">
                    <div class="promo-shapes">
                        <div class="promo-shape promo-shape-1"></div>
                        <div class="promo-shape promo-shape-2"></div>
                        <div class="promo-shape promo-shape-3"></div>
                    </div>
                </div>
                <div class="container">
                    <div class="promo-slide-content">
                        <div class="promo-text-top">
                            <span class="promo-label">НОВИНКИ</span>
                            <span class="promo-label-secondary">2024</span>
                        </div>
                        <div class="promo-main-content">
                            <h2 class="promo-title">Эксклюзивные новинки</h2>
                            <div class="promo-code-wrapper">
                                <div class="promo-code-label">Откройте для себя</div>
                                <div class="promo-code-description">Самые свежие коллекции от ведущих брендов</div>
                            </div>
                            <a href="catalog.php?filter=new" class="btn-promo-catalog">
                                <span>СМОТРЕТЬ НОВИНКИ</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Индикаторы слайдов -->
        <div class="unified-slider-indicators">
            <button type="button" class="unified-indicator unified-indicator-active" data-slide="0" aria-label="Слайд 1"></button>
            <button type="button" class="unified-indicator" data-slide="1" aria-label="Слайд 2"></button>
            <button type="button" class="unified-indicator" data-slide="2" aria-label="Слайд 3"></button>
        </div>
        
        <!-- Стрелки навигации -->
        <button type="button" class="unified-slider-arrow unified-slider-prev" aria-label="Предыдущий слайд">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button type="button" class="unified-slider-arrow unified-slider-next" aria-label="Следующий слайд">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M12 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
</section>

<!-- Секция преимуществ -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card" data-animate="fade-up" data-delay="0">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Оригинальная продукция</h3>
                <p class="feature-text">Только сертифицированные товары от официальных поставщиков</p>
            </div>
            <div class="feature-card" data-animate="fade-up" data-delay="0.1">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Безопасная доставка</h3>
                <p class="feature-text">Надежная упаковка и быстрая доставка в любой город</p>
            </div>
            <div class="feature-card" data-animate="fade-up" data-delay="0.2">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Широкий ассортимент</h3>
                <p class="feature-text">Более 1000 товаров от ведущих мировых брендов косметики</p>
            </div>
            <div class="feature-card" data-animate="fade-up" data-delay="0.3">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Поддержка 24/7</h3>
                <p class="feature-text">Мы всегда готовы помочь вам с выбором и ответить на вопросы</p>
            </div>
        </div>
    </div>
</section>

<!-- Секция ХИТЫ -->
<section class="products-section products-section-hits" data-section="hits">
    <div class="container">
        <div class="section-header">
            <div class="section-title-wrapper">
                <h2 class="section-title">
                    <span class="title-text">ХИТЫ</span>
                    <span class="title-underline"></span>
                </h2>
                <p class="section-subtitle">Самые популярные товары этого сезона</p>
            </div>
            <div class="section-nav">
                <button class="nav-arrow nav-arrow-left" aria-label="Предыдущие">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="nav-arrow nav-arrow-right" aria-label="Следующие">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="products-grid">
            <?php if (!empty($hits)): ?>
                <?php foreach ($hits as $product): ?>
                    <div class="product-card">
                        <?php if ($product['discount'] > 0): ?>
                            <span class="product-badge badge-discount"><?php echo $product['discount']; ?>%</span>
                        <?php else: ?>
                            <span class="product-badge badge-hit">HIT</span>
                        <?php endif; ?>
                        <div class="product-actions">
                            <?php if (isLoggedIn()): ?>
                                <button class="product-favorite <?php echo in_array($product['id'], $favorite_product_ids) ? 'active' : ''; ?>" 
                                        data-product-id="<?php echo $product['id']; ?>" 
                                        aria-label="<?php echo in_array($product['id'], $favorite_product_ids) ? 'Удалить из избранного' : 'В избранное'; ?>">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo in_array($product['id'], $favorite_product_ids) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>
                                <button class="product-cart-btn <?php echo in_array($product['id'], $cart_product_ids) ? 'in-cart' : ''; ?>" 
                                        data-product-id="<?php echo $product['id']; ?>" 
                                        aria-label="<?php echo in_array($product['id'], $cart_product_ids) ? 'В корзине' : 'Добавить в корзину'; ?>">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="product-image-placeholder">Нет фото</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Косметика'); ?></p>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-brand"><?php echo htmlspecialchars($product['brand_name'] ?? ''); ?></p>
                            <p class="product-price">
                                <?php if ($product['old_price']): ?>
                                    <span class="price-old"><?php echo number_format($product['old_price'], 0, ',', ' '); ?> Р</span>
                                <?php endif; ?>
                                <span class="price-current">
                                    <?php echo ($product['old_price'] ? 'от ' : ''); ?>
                                    <?php echo number_format($product['price'], 0, ',', ' '); ?> Р
                                </span>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-products">Товары пока не добавлены</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Секция НОВИНКИ -->
<section class="products-section products-section-new" data-section="new">
    <div class="container">
        <div class="section-header">
            <div class="section-title-wrapper">
                <h2 class="section-title">
                    <span class="title-text">НОВИНКИ</span>
                    <span class="title-underline"></span>
                </h2>
                <p class="section-subtitle">Только что поступившие товары</p>
            </div>
            <div class="section-nav">
                <button class="nav-arrow nav-arrow-left" aria-label="Предыдущие">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="nav-arrow nav-arrow-right" aria-label="Следующие">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="products-grid">
            <?php if (!empty($new_products)): ?>
                <?php foreach ($new_products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['discount'] > 0): ?>
                            <span class="product-badge badge-discount"><?php echo $product['discount']; ?>%</span>
                        <?php else: ?>
                            <span class="product-badge badge-new">NEW</span>
                        <?php endif; ?>
                        <div class="product-actions">
                            <?php if (isLoggedIn()): ?>
                                <button class="product-favorite <?php echo in_array($product['id'], $favorite_product_ids) ? 'active' : ''; ?>" 
                                        data-product-id="<?php echo $product['id']; ?>" 
                                        aria-label="<?php echo in_array($product['id'], $favorite_product_ids) ? 'Удалить из избранного' : 'В избранное'; ?>">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo in_array($product['id'], $favorite_product_ids) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>
                                <button class="product-cart-btn <?php echo in_array($product['id'], $cart_product_ids) ? 'in-cart' : ''; ?>" 
                                        data-product-id="<?php echo $product['id']; ?>" 
                                        aria-label="<?php echo in_array($product['id'], $cart_product_ids) ? 'В корзине' : 'Добавить в корзину'; ?>">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="product-image-placeholder">Нет фото</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Косметика'); ?></p>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-brand"><?php echo htmlspecialchars($product['brand_name'] ?? ''); ?></p>
                            <p class="product-price">
                                <?php if ($product['old_price']): ?>
                                    <span class="price-old"><?php echo number_format($product['old_price'], 0, ',', ' '); ?> Р</span>
                                <?php endif; ?>
                                <span class="price-current">
                                    <?php echo ($product['old_price'] ? 'от ' : ''); ?>
                                    <?php echo number_format($product['price'], 0, ',', ' '); ?> Р
                                </span>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-products">Товары пока не добавлены</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Секция -->
<section class="cta-section">
    <div class="cta-background"></div>
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Не нашли то, что искали?</h2>
            <p class="cta-text">Изучите весь наш каталог и найдите идеальные товары для себя</p>
            <a href="catalog.php" class="btn btn-cta">
                <span>ПОСМОТРЕТЬ ВЕСЬ КАТАЛОГ</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="<?php echo BASE_URL; ?>js/homepage.js?v=<?php echo time(); ?>" defer></script>

