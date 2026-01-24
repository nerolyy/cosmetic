<?php
/**
 * Главная точка входа и роутер
 * Обрабатывает все запросы к страницам
 */

// Определяем запрашиваемый файл
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Убираем query string
$path = parse_url($request_uri, PHP_URL_PATH);

// Убираем базовый путь
$base_path = dirname($script_name);
if ($base_path !== '/' && $base_path !== '\\') {
    $path = str_replace($base_path, '', $path);
}

// Убираем начальный слэш
$path = ltrim($path, '/');

// Убираем index.php из пути
$path = str_replace('index.php', '', $path);
$path = trim($path, '/');

// Если это корень или пусто, загружаем главную страницу
if (empty($path)) {
    require_once __DIR__ . '/pages/index.php';
    exit;
}

// Список доступных страниц
$pages = [
    'catalog' => 'catalog.php',
    'product' => 'product.php',
    'brands' => 'brands.php',
    'shops' => 'shops.php',
    'cart' => 'cart.php',
    'profile' => 'profile.php',
    'login' => 'login.php',
    'register' => 'register.php',
    'logout' => 'logout.php',
];

// Проверяем, есть ли запрашиваемая страница
$page_name = str_replace('.php', '', $path);

if (isset($pages[$page_name])) {
    // Загружаем соответствующую страницу
    $page_file = __DIR__ . '/pages/' . $pages[$page_name];
    if (file_exists($page_file)) {
        require_once $page_file;
        exit;
    }
}

// Если страница не найдена, показываем 404
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница не найдена</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .error-container {
            text-align: center;
            padding: 40px;
        }
        h1 {
            font-size: 72px;
            margin: 0;
            color: #333;
        }
        p {
            font-size: 18px;
            color: #666;
            margin: 20px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <p>Страница не найдена</p>
        <a href="<?php echo $base_path === '/' ? '/' : $base_path . '/'; ?>">Вернуться на главную</a>
    </div>
</body>
</html>
