<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Статистика
$stmt_products = $pdo->query("SELECT COUNT(*) as count FROM products");
$products_count = $stmt_products->fetch()['count'];

$stmt_categories = $pdo->query("SELECT COUNT(*) as count FROM categories");
$categories_count = $stmt_categories->fetch()['count'];

$stmt_brands = $pdo->query("SELECT COUNT(*) as count FROM brands");
$brands_count = $stmt_brands->fetch()['count'];

$stmt_users = $pdo->query("SELECT COUNT(*) as count FROM users");
$users_count = $stmt_users->fetch()['count'];

$stmt_orders = $pdo->query("SELECT COUNT(*) as count FROM orders");
$orders_count = $stmt_orders->fetch()['count'];

$stmt_orders_pending = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$orders_pending_count = $stmt_orders_pending->fetch()['count'];

$stmt_orders_total = $pdo->query("SELECT SUM(total) as total FROM orders WHERE status != 'cancelled'");
$orders_total = $stmt_orders_total->fetch()['total'] ?? 0;

$page_title = 'Админ-панель';
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h2>Админ-панель</h2>
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php" class="admin-nav-link active">Главная</a></li>
                <li><a href="products.php" class="admin-nav-link">Товары</a></li>
                <li><a href="categories.php" class="admin-nav-link">Категории</a></li>
                <li><a href="brands.php" class="admin-nav-link">Бренды</a></li>
                <li><a href="orders.php" class="admin-nav-link">Заказы</a></li>
                <li><a href="users.php" class="admin-nav-link">Пользователи</a></li>
                <li><a href="shops.php" class="admin-nav-link">Магазины</a></li>
                <li><a href="promo_codes.php" class="admin-nav-link">Промокоды</a></li>
                <li><a href="<?php echo BASE_URL; ?>" class="admin-nav-link">На сайт</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Админ-панель</h1>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <h3>Товары</h3>
                <div class="stat-number"><?php echo $products_count; ?></div>
                <a href="products.php" class="stat-link">Управление →</a>
            </div>
            <div class="stat-card">
                <h3>Категории</h3>
                <div class="stat-number"><?php echo $categories_count; ?></div>
                <a href="categories.php" class="stat-link">Управление →</a>
            </div>
            <div class="stat-card">
                <h3>Бренды</h3>
                <div class="stat-number"><?php echo $brands_count; ?></div>
                <a href="brands.php" class="stat-link">Управление →</a>
            </div>
            <div class="stat-card">
                <h3>Пользователи</h3>
                <div class="stat-number"><?php echo $users_count; ?></div>
                <a href="users.php" class="stat-link">Управление →</a>
            </div>
            <div class="stat-card">
                <h3>Заказы</h3>
                <div class="stat-number"><?php echo $orders_count; ?></div>
                <div class="stat-subinfo">Ожидают: <?php echo $orders_pending_count; ?></div>
                <a href="orders.php" class="stat-link">Управление →</a>
            </div>
            <div class="stat-card">
                <h3>Общая сумма заказов</h3>
                <div class="stat-number"><?php echo number_format($orders_total, 0, ',', ' '); ?> Р</div>
                <a href="orders.php" class="stat-link">Детали →</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>



