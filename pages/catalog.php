<?php
require_once __DIR__ . '/../config/config.php';

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

// Получаем минимальную и максимальную цену для фильтра
$stmt_price_range = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products");
$price_range = $stmt_price_range->fetch();
$min_price_db = (float)($price_range['min_price'] ?? 0);
$max_price_db = (float)($price_range['max_price'] ?? 10000);

// Получаем параметры фильтра по цене
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

// Получаем параметр сортировки
$sort = $_GET['sort'] ?? 'newest';

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

// Добавляем фильтр по цене
if ($min_price !== null && $min_price >= 0) {
    $where .= " AND p.price >= ?";
    $params[] = $min_price;
}

if ($max_price !== null && $max_price > 0) {
    $where .= " AND p.price <= ?";
    $params[] = $max_price;
}

// Определяем сортировку
$order_by = "p.created_at DESC"; // По умолчанию - новые сначала
switch ($sort) {
    case 'price_asc':
        $order_by = "p.price ASC";
        break;
    case 'price_desc':
        $order_by = "p.price DESC";
        break;
    case 'name_asc':
        $order_by = "p.name ASC";
        break;
    case 'name_desc':
        $order_by = "p.name DESC";
        break;
    case 'newest':
    default:
        $order_by = "p.created_at DESC";
        break;
}

$sql = "
    SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $where
    ORDER BY $order_by
";
$stmt_products = $pdo->prepare($sql);
$stmt_products->execute($params);
$products = $stmt_products->fetchAll();

$page_title = $current_category ? $current_category['name'] : 'Каталог';
include __DIR__ . '/../includes/header.php';
?>

<div class="catalog-container">
    <div class="catalog-sidebar">
        <nav class="catalog-nav">
            <ul class="catalog-nav-list">
                <?php foreach ($root_categories as $root_cat): ?>
                    <li class="catalog-nav-item <?php echo ($current_category && ($current_category['id'] == $root_cat['id'] || $current_category['parent_id'] == $root_cat['id'])) ? 'active' : ''; ?>">
                        <div class="catalog-nav-link-wrapper">
                            <a href="?category=<?php echo $root_cat['slug']; ?><?php 
                                $query_params = [];
                                if ($min_price !== null) $query_params[] = 'min_price=' . urlencode($min_price);
                                if ($max_price !== null) $query_params[] = 'max_price=' . urlencode($max_price);
                                if ($sort && $sort !== 'newest') $query_params[] = 'sort=' . urlencode($sort);
                                echo !empty($query_params) ? '&' . implode('&', $query_params) : '';
                            ?>" class="catalog-nav-link">
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
                                        <a href="?category=<?php echo $child_cat['slug']; ?><?php 
                                            $query_params = [];
                                            if ($min_price !== null) $query_params[] = 'min_price=' . urlencode($min_price);
                                            if ($max_price !== null) $query_params[] = 'max_price=' . urlencode($max_price);
                                            if ($sort && $sort !== 'newest') $query_params[] = 'sort=' . urlencode($sort);
                                            echo !empty($query_params) ? '&' . implode('&', $query_params) : '';
                                        ?>" class="catalog-nav-link">
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
        
        <!-- Фильтр по цене и сортировка -->
        <div class="catalog-filters">
            <div class="price-filter">
                <form method="GET" action="" class="price-filter-form">
                    <?php if ($category_slug): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>">
                    <?php endif; ?>
                    <?php if ($sort && $sort !== 'newest'): ?>
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    <?php endif; ?>
                    <div class="price-filter-content">
                        <span class="price-filter-label">Цена:</span>
                        <div class="price-inputs">
                            <div class="price-input-group">
                                <label for="min_price">От</label>
                                <input type="number" 
                                       id="min_price" 
                                       name="min_price" 
                                       min="0" 
                                       step="100" 
                                       value="<?php echo $min_price !== null ? htmlspecialchars($min_price) : ''; ?>" 
                                       placeholder="<?php echo number_format($min_price_db, 0, ',', ' '); ?>">
                            </div>
                            <span class="price-separator">—</span>
                            <div class="price-input-group">
                                <label for="max_price">До</label>
                                <input type="number" 
                                       id="max_price" 
                                       name="max_price" 
                                       min="0" 
                                       step="100" 
                                       value="<?php echo $max_price !== null ? htmlspecialchars($max_price) : ''; ?>" 
                                       placeholder="<?php echo number_format($max_price_db, 0, ',', ' '); ?>">
                            </div>
                        </div>
                        <div class="price-filter-actions">
                            <button type="submit" class="btn-filter-apply">Применить</button>
                            <?php if ($min_price !== null || $max_price !== null): ?>
                                <a href="?<?php 
                                    $reset_params = [];
                                    if ($category_slug) $reset_params[] = 'category=' . urlencode($category_slug);
                                    if ($sort && $sort !== 'newest') $reset_params[] = 'sort=' . urlencode($sort);
                                    echo !empty($reset_params) ? implode('&', $reset_params) : '';
                                ?>" class="btn-filter-reset">Сбросить</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Сортировка -->
            <div class="sort-filter">
                <form method="GET" action="" class="sort-filter-form" id="sortForm">
                    <?php if ($category_slug): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>">
                    <?php endif; ?>
                    <?php if ($min_price !== null): ?>
                        <input type="hidden" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>">
                    <?php endif; ?>
                    <?php if ($max_price !== null): ?>
                        <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>">
                    <?php endif; ?>
                    <label for="sort" class="sort-label">Сортировка:</label>
                    <select name="sort" id="sort" class="sort-select">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Сначала новые</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Цена: по возрастанию</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Цена: по убыванию</option>
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Название: А-Я</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Название: Я-А</option>
                    </select>
                </form>
            </div>
        </div>
        
        <div class="products-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
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
                            <a href="<?php echo BASE_URL; ?>product.php?product=<?php echo htmlspecialchars($product['slug']); ?>">
                                <?php if ($product['image']): ?>
                                    <img src="<?php echo UPLOADS_URL . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="product-image-placeholder">Нет фото</div>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="product-info">
                            <a href="<?php echo BASE_URL; ?>product.php?product=<?php echo htmlspecialchars($product['slug']); ?>">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            </a>
                            <p class="product-brand"><?php echo htmlspecialchars($product['brand_name'] ?? ''); ?></p>
                            <p class="product-price"><?php echo number_format($product['price'], 0, ',', ' '); ?> Р</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Товары не найдены</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sort');
    const sortForm = document.getElementById('sortForm');
    
    if (sortSelect && sortForm) {
        sortSelect.addEventListener('change', function() {
            sortForm.submit();
        });
    }
});
</script>

