<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $replacements = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];
    $text = strtr($text, $replacements);
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    $text = trim($text ?? '', '-');
    return $text ?: 'item';
}

function ensureUploadsDir(): string
{
    $dir = rtrim(UPLOADS_PATH, "\\/") . DIRECTORY_SEPARATOR;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function createPackshotSvg(string $filename, string $brand, string $name, string $categoryLabel): void
{
    $uploadsDir = ensureUploadsDir();
    $path = $uploadsDir . $filename;

    $brandEsc = htmlspecialchars($brand, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $nameEsc = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $catEsc = htmlspecialchars($categoryLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="900" height="900" viewBox="0 0 900 900">
  <defs>
    <linearGradient id="g" x1="120" y1="120" x2="780" y2="820" gradientUnits="userSpaceOnUse">
      <stop offset="0" stop-color="#ffffff"/>
      <stop offset="1" stop-color="#fde7f1"/>
    </linearGradient>
    <linearGradient id="stroke" x1="0" y1="0" x2="900" y2="900" gradientUnits="userSpaceOnUse">
      <stop offset="0" stop-color="#ec407a" stop-opacity="0.55"/>
      <stop offset="1" stop-color="#d81b60" stop-opacity="0.22"/>
    </linearGradient>
    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="18" stdDeviation="22" flood-color="#0b1220" flood-opacity="0.12"/>
    </filter>
  </defs>

  <rect x="0" y="0" width="900" height="900" fill="url(#g)"/>
  <circle cx="190" cy="150" r="130" fill="#ec407a" fill-opacity="0.10"/>
  <circle cx="760" cy="760" r="190" fill="#d81b60" fill-opacity="0.08"/>

  <g filter="url(#shadow)">
    <rect x="150" y="150" width="600" height="600" rx="44" fill="#ffffff" stroke="url(#stroke)" stroke-width="3"/>
  </g>

  <text x="450" y="300" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="44" font-weight="800" fill="#121826">{$brandEsc}</text>
  <text x="450" y="360" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="18" font-weight="700" fill="#d81b60" letter-spacing="2">{$catEsc}</text>

  <foreignObject x="210" y="410" width="480" height="210">
    <div xmlns="http://www.w3.org/1999/xhtml" style="font-family: Segoe UI, Arial, sans-serif; font-size: 26px; line-height: 1.25; font-weight: 700; color: #121826; text-align: center;">
      {$nameEsc}
    </div>
  </foreignObject>

  <rect x="260" y="650" width="380" height="16" rx="8" fill="#f8bbd0" opacity="0.7"/>
  <rect x="260" y="650" width="220" height="16" rx="8" fill="#ec407a" opacity="0.55"/>

  <text x="450" y="720" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="14" fill="#6b7280">изображение‑заглушка • packshot</text>
</svg>
SVG;

    file_put_contents($path, $svg);
}

$page_title = 'Seed каталога';
include __DIR__ . '/../includes/header.php';

$done = false;
$stats = [
    'brands_upserted' => 0,
    'products_deleted' => 0,
    'products_inserted' => 0,
    'images_created' => 0,
    'skipped_categories' => [],
];
$error = '';

// Категории из ассортимента (как на скрине)
$assortmentCategorySlugs = [
    // Волосы
    'konditsionery-i-balzamy' => 'Волосы',
    'stayling' => 'Волосы',
    'suhie-shampuni' => 'Волосы',
    // Макияж
    'glaza' => 'Макияж',
    'guby' => 'Макияж',
    'litso' => 'Макияж',
    // Маникюр
    'gel-laki' => 'Маникюр',
    'laki-dlya-nogtey' => 'Маникюр',
    'sredstva-dlya-snyatiya-laka' => 'Маникюр',
    // Мужчины
    'britie' => 'Мужчины',
    'volosy-muzhchiny' => 'Мужчины',
    'uhod-muzhchiny' => 'Мужчины',
    // Парфюмерия
    'zhenskie-aromaty' => 'Парфюмерия',
    'muzhskie-aromaty' => 'Парфюмерия',
    'uniseks-aromaty' => 'Парфюмерия',
];

// Реальные бренды (под разные разделы)
$brands = [
    ['name' => 'Dior', 'slug' => 'dior'],
    ['name' => 'Chanel', 'slug' => 'chanel'],
    ['name' => 'Yves Saint Laurent', 'slug' => 'ysl'],
    ['name' => 'MAC', 'slug' => 'mac'],
    ['name' => 'NARS', 'slug' => 'nars'],
    ['name' => 'Estée Lauder', 'slug' => 'estee-lauder'],
    ['name' => 'Maybelline', 'slug' => 'maybelline'],
    ['name' => 'Kérastase', 'slug' => 'kerastase'],
    ['name' => 'Olaplex', 'slug' => 'olaplex'],
    ['name' => 'Moroccanoil', 'slug' => 'moroccanoil'],
    ['name' => 'Batiste', 'slug' => 'batiste'],
    ['name' => "L'Oréal Professionnel", 'slug' => 'loreal-professionnel'],
    ['name' => 'OPI', 'slug' => 'opi'],
    ['name' => 'essie', 'slug' => 'essie'],
    ['name' => 'Sally Hansen', 'slug' => 'sally-hansen'],
    ['name' => 'Calvin Klein', 'slug' => 'calvin-klein'],
    ['name' => 'Versace', 'slug' => 'versace'],
    ['name' => 'Giorgio Armani', 'slug' => 'giorgio-armani'],
    ['name' => 'Hugo Boss', 'slug' => 'hugo-boss'],
    ['name' => 'NIVEA MEN', 'slug' => 'nivea-men'],
    ['name' => 'Old Spice', 'slug' => 'old-spice'],
    ['name' => 'Gillette', 'slug' => 'gillette'],
];

// Товарные шаблоны (по 3–4 на категорию)
$templates = [
    // Волосы
    'konditsionery-i-balzamy' => [
        ['brand' => 'kerastase', 'name' => 'Nutritive Lait Vital', 'desc' => 'Увлажняющий кондиционер для сухих волос. Делает волосы мягкими и послушными.', 'price' => 3490, 'old' => 3990, 'new' => 1, 'featured' => 1],
        ['brand' => 'olaplex', 'name' => 'No.5 Bond Maintenance Conditioner', 'desc' => 'Кондиционер для восстановления связей. Подходит окрашенным и повреждённым волосам.', 'price' => 3290, 'old' => null, 'new' => 0, 'featured' => 1],
        ['brand' => 'moroccanoil', 'name' => 'Hydrating Conditioner', 'desc' => 'Кондиционер с аргановым маслом для увлажнения и блеска.', 'price' => 2890, 'old' => 3290, 'new' => 1, 'featured' => 0],
        ['brand' => 'loreal-professionnel', 'name' => 'Serie Expert Absolut Repair Conditioner', 'desc' => 'Питательный кондиционер для восстановления и гладкости.', 'price' => 2190, 'old' => null, 'new' => 0, 'featured' => 0],
    ],
    'suhie-shampuni' => [
        ['brand' => 'batiste', 'name' => 'Dry Shampoo Original', 'desc' => 'Сухой шампунь для свежести у корней и объёма за минуту.', 'price' => 690, 'old' => 890, 'new' => 1, 'featured' => 1],
        ['brand' => 'batiste', 'name' => 'Dry Shampoo Blush', 'desc' => 'Сухой шампунь с лёгким ароматом, быстро освежает укладку.', 'price' => 690, 'old' => null, 'new' => 0, 'featured' => 0],
        ['brand' => 'loreal-professionnel', 'name' => 'Tecni.Art Morning After Dust', 'desc' => 'Пудровый сухой шампунь для текстуры и свежести.', 'price' => 1590, 'old' => 1890, 'new' => 0, 'featured' => 1],
    ],
    'stayling' => [
        ['brand' => 'moroccanoil', 'name' => 'Perfect Defense Heat Protectant', 'desc' => 'Термозащита в спрее: защищает от горячих инструментов и добавляет мягкость.', 'price' => 2890, 'old' => 3290, 'new' => 1, 'featured' => 1],
        ['brand' => 'loreal-professionnel', 'name' => 'Tecni.Art Fix Anti‑Frizz Spray', 'desc' => 'Лак‑спрей для фиксации и защиты от пушистости.', 'price' => 1490, 'old' => null, 'new' => 0, 'featured' => 0],
        ['brand' => 'kerastase', 'name' => 'Discipline Keratine Thermique', 'desc' => 'Крем‑термозащита для гладкости и дисциплины волос.', 'price' => 3490, 'old' => 3990, 'new' => 0, 'featured' => 1],
    ],

    // Макияж
    'litso' => [
        ['brand' => 'estee-lauder', 'name' => 'Double Wear Stay‑in‑Place', 'desc' => 'Легендарный тональный крем с высокой стойкостью и естественным матовым финишем.', 'price' => 3890, 'old' => 4290, 'new' => 1, 'featured' => 1],
        ['brand' => 'dior', 'name' => 'Forever Skin Glow', 'desc' => 'Тональный крем с естественным сиянием и стойкостью на весь день.', 'price' => 4190, 'old' => null, 'new' => 0, 'featured' => 1],
        ['brand' => 'nars', 'name' => 'Radiant Creamy Concealer', 'desc' => 'Консилер со средним покрытием и эффектом свежей кожи.', 'price' => 2790, 'old' => null, 'new' => 0, 'featured' => 0],
        ['brand' => 'mac', 'name' => 'Fix+ Setting Spray', 'desc' => 'Фиксирующий спрей: освежает макияж и помогает “усадить” пудру.', 'price' => 2490, 'old' => 2790, 'new' => 1, 'featured' => 0],
    ],
    'glaza' => [
        ['brand' => 'dior', 'name' => 'Diorshow Iconic Overcurl', 'desc' => 'Тушь для объёма и подкручивания, выразительный взгляд без комочков.', 'price' => 3590, 'old' => null, 'new' => 1, 'featured' => 1],
        ['brand' => 'maybelline', 'name' => 'Lash Sensational', 'desc' => 'Тушь для веерного объёма и разделения, стойкость на каждый день.', 'price' => 990, 'old' => 1290, 'new' => 0, 'featured' => 0],
        ['brand' => 'ysl', 'name' => 'Couture Eyeliner', 'desc' => 'Жидкая подводка для чёткой стрелки и стойкости.', 'price' => 3190, 'old' => null, 'new' => 0, 'featured' => 1],
    ],
    'guby' => [
        ['brand' => 'dior', 'name' => 'Rouge Dior', 'desc' => 'Классическая помада с комфортной текстурой и насыщенным цветом.', 'price' => 3990, 'old' => 4390, 'new' => 1, 'featured' => 1],
        ['brand' => 'chanel', 'name' => 'Rouge Coco', 'desc' => 'Увлажняющая помада с сияющим финишем и удобным нанесением.', 'price' => 4190, 'old' => null, 'new' => 0, 'featured' => 1],
        ['brand' => 'maybelline', 'name' => 'SuperStay Matte Ink', 'desc' => 'Стойкая матовая жидкая помада, держится до 16 часов.', 'price' => 990, 'old' => 1190, 'new' => 0, 'featured' => 0],
    ],

    // Маникюр
    'gel-laki' => [
        ['brand' => 'opi', 'name' => 'GelColor Bubble Bath', 'desc' => 'Гель‑лак нежного нюдового оттенка. Ровное покрытие и стойкость.', 'price' => 1690, 'old' => 1990, 'new' => 1, 'featured' => 1],
        ['brand' => 'essie', 'name' => 'Gel Couture Sheer Fantasy', 'desc' => 'Гель‑эффект без лампы: чистый цвет и блеск.', 'price' => 1190, 'old' => null, 'new' => 0, 'featured' => 0],
        ['brand' => 'sally-hansen', 'name' => 'Miracle Gel Red Eye', 'desc' => 'Яркий цвет и глянцевый финиш, удобная кисть.', 'price' => 990, 'old' => 1290, 'new' => 0, 'featured' => 0],
    ],
    'laki-dlya-nogtey' => [
        ['brand' => 'essie', 'name' => 'Ballet Slippers', 'desc' => 'Лак‑классика для аккуратного нюда и “маникюра без ошибок”.', 'price' => 790, 'old' => 990, 'new' => 0, 'featured' => 1],
        ['brand' => 'opi', 'name' => 'Big Apple Red', 'desc' => 'Классический красный лак с плотным покрытием.', 'price' => 990, 'old' => null, 'new' => 1, 'featured' => 0],
        ['brand' => 'sally-hansen', 'name' => 'Insta‑Dri Quick Dry', 'desc' => 'Быстросохнущий лак для идеального результата за считанные минуты.', 'price' => 690, 'old' => 890, 'new' => 0, 'featured' => 0],
    ],
    'sredstva-dlya-snyatiya-laka' => [
        ['brand' => 'essie', 'name' => 'Nail Polish Remover', 'desc' => 'Средство для снятия лака без пересушивания ногтевой пластины.', 'price' => 590, 'old' => null, 'new' => 0, 'featured' => 0],
        ['brand' => 'opi', 'name' => 'Expert Touch Lacquer Remover', 'desc' => 'Профессиональное средство для снятия лака (ацетоновое).', 'price' => 790, 'old' => 990, 'new' => 1, 'featured' => 0],
        ['brand' => 'sally-hansen', 'name' => 'Remover with Vitamin E', 'desc' => 'Снятие лака + витамин Е для более мягкого ухода.', 'price' => 690, 'old' => null, 'new' => 0, 'featured' => 1],
    ],

    // Мужчины
    'britie' => [
        ['brand' => 'gillette', 'name' => 'Fusion5 Razor', 'desc' => 'Станок с 5 лезвиями для комфортного бритья.', 'price' => 1490, 'old' => 1790, 'new' => 0, 'featured' => 1],
        ['brand' => 'nivea-men', 'name' => 'Sensitive Shaving Gel', 'desc' => 'Гель для бритья для чувствительной кожи: меньше раздражения.', 'price' => 490, 'old' => null, 'new' => 1, 'featured' => 0],
        ['brand' => 'old-spice', 'name' => 'After Shave Lotion', 'desc' => 'Лосьон после бритья для свежести и комфорта кожи.', 'price' => 590, 'old' => 690, 'new' => 0, 'featured' => 0],
    ],
    'volosy-muzhchiny' => [
        ['brand' => 'loreal-professionnel', 'name' => 'Homme Shampoo', 'desc' => 'Шампунь для ежедневного очищения и свежести.', 'price' => 1090, 'old' => null, 'new' => 0, 'featured' => 0],
        ['brand' => 'nivea-men', 'name' => 'Shampoo Deep', 'desc' => 'Шампунь с очищающим эффектом, подходит для спорта и города.', 'price' => 390, 'old' => 490, 'new' => 1, 'featured' => 0],
        ['brand' => 'old-spice', 'name' => '2‑in‑1 Shampoo + Conditioner', 'desc' => 'Удобный 2‑в‑1 формат для дороги и зала.', 'price' => 490, 'old' => null, 'new' => 0, 'featured' => 1],
    ],
    'uhod-muzhchiny' => [
        ['brand' => 'nivea-men', 'name' => 'Deodorant Dry Impact', 'desc' => 'Дезодорант‑антиперспирант для защиты и свежести.', 'price' => 390, 'old' => 490, 'new' => 0, 'featured' => 1],
        ['brand' => 'old-spice', 'name' => 'Deodorant Stick', 'desc' => 'Стик‑дезодорант, классический аромат и комфорт.', 'price' => 490, 'old' => null, 'new' => 1, 'featured' => 0],
        ['brand' => 'gillette', 'name' => 'Cooling Body Wash', 'desc' => 'Гель для душа с охлаждающим эффектом после тренировки.', 'price' => 590, 'old' => null, 'new' => 0, 'featured' => 0],
    ],

    // Парфюмерия
    'zhenskie-aromaty' => [
        ['brand' => 'chanel', 'name' => 'Coco Mademoiselle Eau de Parfum', 'desc' => 'Классический женственный аромат: цитрусы, роза, пачули.', 'price' => 11990, 'old' => 13490, 'new' => 1, 'featured' => 1],
        ['brand' => 'dior', 'name' => 'J’adore Eau de Parfum', 'desc' => 'Цветочный аромат с элегантным шлейфом.', 'price' => 10990, 'old' => null, 'new' => 0, 'featured' => 1],
        ['brand' => 'versace', 'name' => 'Bright Crystal Eau de Toilette', 'desc' => 'Лёгкий свежий аромат на каждый день.', 'price' => 6990, 'old' => 7990, 'new' => 0, 'featured' => 0],
    ],
    'muzhskie-aromaty' => [
        ['brand' => 'dior', 'name' => 'Sauvage Eau de Parfum', 'desc' => 'Древесно‑ароматический мужской бестселлер.', 'price' => 10490, 'old' => 11990, 'new' => 1, 'featured' => 1],
        ['brand' => 'giorgio-armani', 'name' => 'Acqua di Giò Eau de Toilette', 'desc' => 'Свежий морской аромат: цитрусы и древесные ноты.', 'price' => 8990, 'old' => null, 'new' => 0, 'featured' => 1],
        ['brand' => 'hugo-boss', 'name' => 'BOSS Bottled Eau de Toilette', 'desc' => 'Классический аромат: яблоко, специи, древесные ноты.', 'price' => 7490, 'old' => 8490, 'new' => 0, 'featured' => 0],
    ],
    'uniseks-aromaty' => [
        ['brand' => 'calvin-klein', 'name' => 'CK One Eau de Toilette', 'desc' => 'Культовый унисекс‑аромат: свежий, чистый, повседневный.', 'price' => 4990, 'old' => 5990, 'new' => 0, 'featured' => 1],
        ['brand' => 'ysl', 'name' => 'Libre Eau de Parfum', 'desc' => 'Современный аромат с лавандой и ванилью (универсально, со шлейфом).', 'price' => 10990, 'old' => null, 'new' => 1, 'featured' => 0],
        ['brand' => 'chanel', 'name' => 'Chance Eau Tendre', 'desc' => 'Свежий цветочно‑фруктовый аромат, лёгкий и деликатный.', 'price' => 9990, 'old' => 11290, 'new' => 0, 'featured' => 0],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_seed']) && $_POST['do_seed'] === '1') {
    try {
        $pdo->beginTransaction();

        // 1) Бренды
        $stmtBrand = $pdo->prepare("INSERT INTO brands (name, slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name)");
        foreach ($brands as $b) {
            $stmtBrand->execute([$b['name'], $b['slug']]);
            $stats['brands_upserted']++;
        }

        $brandRows = $pdo->query("SELECT id, slug FROM brands")->fetchAll();
        $brandIdBySlug = [];
        foreach ($brandRows as $r) {
            $brandIdBySlug[$r['slug']] = (int)$r['id'];
        }

        // 2) Удаляем товары
        $stats['products_deleted'] = (int)$pdo->exec("DELETE FROM products");

        // 3) Подготовим категории
        $catRows = $pdo->query("SELECT id, slug, name FROM categories WHERE is_hidden IS NULL OR is_hidden = 0")->fetchAll();
        $catBySlug = [];
        foreach ($catRows as $r) {
            $catBySlug[$r['slug']] = ['id' => (int)$r['id'], 'name' => (string)$r['name']];
        }

        $insertProduct = $pdo->prepare("
            INSERT INTO products
                (name, slug, description, price, old_price, discount, image, category_id, brand_id, stock, is_new, is_featured)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $usedSlugs = [];

        foreach ($assortmentCategorySlugs as $catSlug => $rootLabel) {
            if (!isset($catBySlug[$catSlug])) {
                $stats['skipped_categories'][] = $catSlug;
                continue;
            }

            $catId = $catBySlug[$catSlug]['id'];
            $catName = $catBySlug[$catSlug]['name'];
            $categoryLabel = $rootLabel . ' • ' . $catName;

            $items = $templates[$catSlug] ?? [];
            if (empty($items)) {
                $stats['skipped_categories'][] = $catSlug;
                continue;
            }

            // берём 3–4 товара: если есть 4 — все 4, иначе как есть
            $items = array_slice($items, 0, 4);

            foreach ($items as $i => $tpl) {
                $brandSlug = $tpl['brand'];
                if (!isset($brandIdBySlug[$brandSlug])) {
                    continue;
                }

                $brandId = $brandIdBySlug[$brandSlug];

                $fullName = $tpl['name'];
                $brandNameRow = $pdo->prepare("SELECT name FROM brands WHERE id = ? LIMIT 1");
                $brandNameRow->execute([$brandId]);
                $brandName = (string)($brandNameRow->fetchColumn() ?: 'Brand');

                $productName = $brandName . ' ' . $fullName;

                $baseSlug = slugify($brandName) . '-' . slugify($fullName) . '-' . $catSlug;
                $slug = $baseSlug;
                $n = 2;
                while (isset($usedSlugs[$slug])) {
                    $slug = $baseSlug . '-' . $n;
                    $n++;
                }
                $usedSlugs[$slug] = true;

                $price = (float)$tpl['price'];
                $oldPrice = $tpl['old'] !== null ? (float)$tpl['old'] : null;
                $discount = 0;
                if ($oldPrice !== null && $oldPrice > 0 && $oldPrice > $price) {
                    $discount = (int)round((($oldPrice - $price) / $oldPrice) * 100);
                }

                $stock = 6 + (($i * 3) % 11);
                $isNew = (int)($tpl['new'] ?? 0);
                $isFeatured = (int)($tpl['featured'] ?? 0);

                $imageFilename = 'seed_' . $slug . '.svg';
                createPackshotSvg($imageFilename, $brandName, $fullName, $categoryLabel);
                $stats['images_created']++;

                $insertProduct->execute([
                    $productName,
                    $slug,
                    (string)$tpl['desc'],
                    $price,
                    $oldPrice,
                    $discount,
                    $imageFilename,
                    $catId,
                    $brandId,
                    $stock,
                    $isNew,
                    $isFeatured,
                ]);
                $stats['products_inserted']++;
            }
        }

        $pdo->commit();
        $done = true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>

<div class="container" style="padding: 32px 0;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h1 style="margin-bottom: 10px;">Seed каталога</h1>
        <p style="color: var(--text-light); margin-bottom: 18px; line-height: 1.6;">
            Скрипт удалит <strong>все товары</strong> и создаст по <strong>3–4 товара</strong> в каждую подкатегорию из ассортимента на главном меню
            (Волосы / Макияж / Маникюр / Мужчины / Парфюмерия). Также будут добавлены реальные бренды и сгенерированы изображения‑заглушки в <code>uploads/</code>.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 16px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($done): ?>
            <div class="alert alert-success" style="margin-bottom: 16px;">
                Seed выполнен.
            </div>
            <div class="beauty-guide-card" style="padding: 18px;">
                <div><strong>Брендов добавлено/обновлено:</strong> <?php echo (int)$stats['brands_upserted']; ?></div>
                <div><strong>Товаров удалено:</strong> <?php echo (int)$stats['products_deleted']; ?></div>
                <div><strong>Товаров добавлено:</strong> <?php echo (int)$stats['products_inserted']; ?></div>
                <div><strong>Изображений создано:</strong> <?php echo (int)$stats['images_created']; ?></div>
                <?php if (!empty($stats['skipped_categories'])): ?>
                    <div style="margin-top: 10px; color: var(--text-light);">
                        <strong>Пропущенные категории (не найдены в БД):</strong>
                        <?php echo htmlspecialchars(implode(', ', $stats['skipped_categories'])); ?>
                    </div>
                <?php endif; ?>
                <div style="margin-top: 12px;">
                    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>catalog.php">Открыть каталог</a>
                    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>admin/products.php" style="margin-left: 10px;">В админ‑товары</a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" onsubmit="return confirm('Удалить ВСЕ товары и создать новые?');">
                <input type="hidden" name="do_seed" value="1">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Запустить seed (удалит все товары)</button>
            </form>
            <div style="margin-top: 10px; color: var(--text-light); font-size: 13px;">
                Рекомендация: сначала убедитесь, что категории из <code>data.sql</code> импортированы (через <code>install.php</code> или phpMyAdmin).
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

