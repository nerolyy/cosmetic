<?php
require_once __DIR__ . '/../config/config.php';

// Получаем фильтр из URL
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

// Получаем избранные бренды пользователя
$favorite_brand_ids = [];
if (isLoggedIn()) {
    $stmt_fav = $pdo->prepare("SELECT brand_id FROM brand_favorites WHERE user_id = ?");
    $stmt_fav->execute([$_SESSION['user_id']]);
    $favorite_brand_ids = array_column($stmt_fav->fetchAll(), 'brand_id');
}

// Получаем все бренды
$where = "1=1";
$params = [];

if ($search) {
    $where = "b.name LIKE ?";
    $params[] = "%$search%";
} elseif ($filter !== 'all' && $filter !== '') {
    if ($filter === '0-9') {
        $where = "b.name REGEXP '^[0-9]'";
    } elseif ($filter === 'А-Я') {
        $where = "b.name REGEXP '^[А-Яа-яЁё]'";
    } elseif (strlen($filter) === 1 && ctype_alpha($filter)) {
        $where = "UPPER(SUBSTRING(b.name, 1, 1)) = ?";
        $params[] = strtoupper($filter);
    }
}

$sql = "SELECT b.*, 
        CASE WHEN bf.id IS NOT NULL THEN 1 ELSE 0 END as is_favorite
        FROM brands b 
        LEFT JOIN brand_favorites bf ON b.id = bf.brand_id AND bf.user_id = ?
        WHERE $where
        ORDER BY b.name";
        
$params = array_merge([isLoggedIn() ? $_SESSION['user_id'] : 0], $params);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$brands = $stmt->fetchAll();

// Группируем бренды по первой букве/цифре
$grouped_brands = [];
foreach ($brands as $brand) {
    $first_char = mb_strtoupper(mb_substr($brand['name'], 0, 1, 'UTF-8'), 'UTF-8');
    
    // Определяем группу
    if (preg_match('/[0-9]/', $first_char)) {
        $group = '0-9';
    } elseif (preg_match('/[А-Я]/u', $first_char)) {
        $group = 'А-Я';
    } elseif (preg_match('/[A-Z]/', $first_char)) {
        $group = $first_char;
    } else {
        $group = '0-9';
    }
    
    if (!isset($grouped_brands[$group])) {
        $grouped_brands[$group] = [];
    }
    $grouped_brands[$group][] = $brand;
}

// Получаем избранные бренды
$favorite_brands = [];
if (isLoggedIn() && !empty($favorite_brand_ids)) {
    $placeholders = str_repeat('?,', count($favorite_brand_ids) - 1) . '?';
    $stmt_fav = $pdo->prepare("SELECT * FROM brands WHERE id IN ($placeholders) ORDER BY name");
    $stmt_fav->execute($favorite_brand_ids);
    $favorite_brands = $stmt_fav->fetchAll();
}

$page_title = 'Бренды';
include __DIR__ . '/../includes/header.php';
?>

<div class="brands-container">
    <!-- Фильтр по алфавиту -->
    <div class="brands-filter">
        <div class="container">
            <div class="alphabet-filter">
                <a href="?filter=0-9" class="filter-link <?php echo $filter === '0-9' ? 'active' : ''; ?>">0—9</a>
                <?php foreach (range('A', 'Z') as $letter): ?>
                    <a href="?filter=<?php echo $letter; ?>" class="filter-link <?php echo $filter === $letter ? 'active' : ''; ?>"><?php echo $letter; ?></a>
                <?php endforeach; ?>
                <a href="?filter=А-Я" class="filter-link <?php echo $filter === 'А-Я' ? 'active' : ''; ?>">А—Я</a>
            </div>
        </div>
    </div>

    <!-- Поиск брендов -->
    <div class="brands-search">
        <div class="container">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="найти бренды" value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <button type="submit" class="search-button">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
                <?php if ($search || $filter !== 'all'): ?>
                    <a href="brands.php" class="clear-filter">Сбросить</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="brands-content">
        <div class="container">
            <div class="brands-layout">
                <!-- Левая колонка: Избранные -->
                <div class="brands-favorites">
                    <h2 class="section-title">избранные</h2>
                    <?php if (!empty($favorite_brands)): ?>
                        <ul class="brands-list">
                            <?php foreach ($favorite_brands as $brand): ?>
                                <li class="brand-item">
                                    <a href="catalog.php?brand=<?php echo $brand['slug']; ?>" class="brand-link">
                                        <?php echo htmlspecialchars($brand['name']); ?>
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <button class="brand-favorite-btn active" data-brand-id="<?php echo $brand['id']; ?>" aria-label="Удалить из избранного">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-message">Пока здесь ничего нет. Добавляйте новые бренды в своё избранное</p>
                    <?php endif; ?>
                </div>

                <!-- Правая колонка: Список брендов -->
                <div class="brands-list-section">
                    <?php if (!empty($grouped_brands)): ?>
                        <?php foreach ($grouped_brands as $group => $group_brands): ?>
                            <div class="brand-group">
                                <h2 class="group-title"><?php echo htmlspecialchars($group); ?></h2>
                                <ul class="brands-list two-columns">
                                    <?php foreach ($group_brands as $brand): ?>
                                        <li class="brand-item">
                                            <a href="catalog.php?brand=<?php echo $brand['slug']; ?>" class="brand-link">
                                                <?php echo htmlspecialchars($brand['name']); ?>
                                            </a>
                                            <?php if (isLoggedIn()): ?>
                                                <button class="brand-favorite-btn <?php echo $brand['is_favorite'] ? 'active' : ''; ?>" 
                                                        data-brand-id="<?php echo $brand['id']; ?>" 
                                                        aria-label="<?php echo $brand['is_favorite'] ? 'Удалить из избранного' : 'Добавить в избранное'; ?>">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $brand['is_favorite'] ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-message">Бренды не найдены</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

