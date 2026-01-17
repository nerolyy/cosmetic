<?php
require_once 'config.php';

// Получаем текущую категорию
$category_slug = $_GET['category'] ?? null;
$current_category = null;

if ($category_slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$category_slug]);
    $current_category = $stmt->fetch();
}

// Получаем все корневые категории (не скрытые)
$stmt_root = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL AND (is_hidden IS NULL OR is_hidden = 0) ORDER BY name");
$root_categories = $stmt_root->fetchAll();

// Получаем дочерние категории (не скрытые)
$stmt_child = $pdo->query("SELECT * FROM categories WHERE parent_id IS NOT NULL AND (is_hidden IS NULL OR is_hidden = 0) ORDER BY name");
$child_categories_data = $stmt_child->fetchAll();
$child_categories = [];
foreach ($child_categories_data as $child) {
    $child_categories[$child['parent_id']][] = $child;
}

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

// Формируем WHERE условие
$where = "1=1";
$params = [];

if ($current_category) {
    $category_ids = [$current_category['id']];
    if (isset($child_categories[$current_category['id']])) {
        foreach ($child_categories[$current_category['id']] as $child) {
            $category_ids[] = $child['id'];
        }
    }
    $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
    $where = "p.category_id IN ($placeholders)";
    $params = $category_ids;
}

$sql = "
    SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $where
    ORDER BY p.created_at DESC
";
$stmt_products = $pdo->prepare($sql);
$stmt_products->execute($params);
$products = $stmt_products->fetchAll();

$page_title = $current_category ? $current_category['name'] : 'Каталог';
include 'includes/header.php';
?>

<div class="catalog-container">
    <div class="catalog-sidebar">
        <nav class="catalog-nav">
            <ul class="catalog-nav-list">
                <?php foreach ($root_categories as $root_cat): ?>
                    <li class="catalog-nav-item <?php echo ($current_category && ($current_category['id'] == $root_cat['id'] || $current_category['parent_id'] == $root_cat['id'])) ? 'active' : ''; ?>">
                        <div class="catalog-nav-link-wrapper">
                            <a href="?category=<?php echo $root_cat['slug']; ?>" class="catalog-nav-link">
                                <?php echo htmlspecialchars($root_cat['name']); ?>
                            </a>
                            <?php if (isset($child_categories[$root_cat['id']])): ?>
                                <button class="catalog-nav-toggle" aria-label="Раскрыть подкатегории">
                                    <span class="nav-arrow">›</span>
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($child_categories[$root_cat['id']])): ?>
                            <ul class="catalog-nav-submenu" style="display: <?php echo ($current_category && ($current_category['id'] == $root_cat['id'] || $current_category['parent_id'] == $root_cat['id'])) ? 'block' : 'none'; ?>;">
                                <?php foreach ($child_categories[$root_cat['id']] as $child_cat): ?>
                                    <li class="catalog-nav-subitem <?php echo ($current_category && $current_category['id'] == $child_cat['id']) ? 'active' : ''; ?>">
                                        <a href="?category=<?php echo $child_cat['slug']; ?>" class="catalog-nav-link">
                                            <?php echo htmlspecialchars($child_cat['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>

    <div class="catalog-content">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        
        <div class="products-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="product-card" data-product-id="<?php echo $product['id']; ?>">
                        <?php if ($product['discount'] > 0): ?>
                            <span class="product-badge badge-discount"><?php echo $product['discount']; ?>%</span>
                        <?php elseif ($product['is_new']): ?>
                            <span class="product-badge badge-new">NEW</span>
                        <?php elseif ($product['is_featured']): ?>
                            <span class="product-badge badge-hit">HIT</span>
                        <?php endif; ?>
                        <div class="product-actions" onclick="event.preventDefault(); event.stopPropagation();">
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
                            <?php if (!empty($product['description'])): ?>
                                <p class="product-description-short"><?php echo htmlspecialchars(mb_substr($product['description'], 0, 80)) . (mb_strlen($product['description']) > 80 ? '...' : ''); ?></p>
                            <?php endif; ?>
                            <div class="product-meta">
                                <span class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo $product['stock'] > 0 ? 'В наличии (' . $product['stock'] . ')' : 'Нет в наличии'; ?>
                                </span>
                            </div>
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
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Товары не найдены</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

