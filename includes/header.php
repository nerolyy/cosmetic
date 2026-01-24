<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Косметика</title>
    <!-- CSS разбито по модулям: базовые стили, layout, компоненты, страницы -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/base.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/components.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/pages.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css?v=<?php echo time(); ?>">
    <script src="<?php echo ASSETS_URL; ?>js/favorites.js" defer></script>
    <script src="<?php echo ASSETS_URL; ?>js/cart.js" defer></script>
    <script src="<?php echo ASSETS_URL; ?>js/product.js" defer></script>
</head>
<body>
    <header class="main-header">
        <div class="header-top-bar">
            <div class="container">
                <div class="header-top-content">
                    <a href="<?php echo BASE_URL; ?>" class="logo">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                    </a>
                    <nav class="nav-menu">
                        <a href="<?php echo BASE_URL; ?>catalog.php">каталог</a>
                        <a href="<?php echo BASE_URL; ?>brands.php">бренды</a>
                        <a href="<?php echo BASE_URL; ?>">новинки</a>
                        
                        <a href="<?php echo BASE_URL; ?>shops.php">магазины</a>
                    </nav>
                    <div class="header-icons">
                        <a href="<?php echo BASE_URL; ?>catalog.php" class="icon-link" title="Поиск">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </a>
                        <a href="<?php echo BASE_URL; ?>profile.php" class="icon-link" title="Избранное">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </a>
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo BASE_URL; ?>admin/" class="icon-link" title="Админ-панель">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>profile.php" class="icon-link" title="Профиль">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>login.php" class="icon-link" title="Войти">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>cart.php" class="icon-link" title="Корзина">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">

