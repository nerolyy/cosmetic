<?php
require_once __DIR__ . '/../config/config.php';

$page_title = 'Колесо фортуны';
$error = '';
$success = '';
$spinResult = null;
$spinSectorIndex = null;
$spinSectorAngle = null;
$wheelSectors = [];
$wheelSectorLabels = [
    'Бренд',
    'Категория',
    'Товар',
    'Бренд',
    'Категория',
    'Товар',
    'Бренд',
    'Категория',
];

function wheelShortLabel(string $value, int $max = 16): string
{
    $value = trim($value);
    if ($value === '') {
        return $value;
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($value, 'UTF-8') > $max
            ? mb_substr($value, 0, $max - 1, 'UTF-8') . '...'
            : $value;
    }
    return strlen($value) > $max ? substr($value, 0, $max - 1) . '...' : $value;
}

function wheelGeneratePromoCode(PDO $pdo): string
{
    for ($i = 0; $i < 10; $i++) {
        $code = 'WHEEL' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $pdo->prepare('SELECT id FROM wheel_rewards WHERE promo_code = ? LIMIT 1');
        $stmt->execute([$code]);
        if (!$stmt->fetch()) {
            return $code;
        }
    }
    return 'WHEEL' . strtoupper(bin2hex(random_bytes(4)));
}

function wheelPickReward(PDO $pdo): ?array
{
    $types = ['brand', 'brand', 'category', 'category', 'product'];
    $type = $types[array_rand($types)];

    if ($type === 'brand') {
        $row = $pdo->query('SELECT id, name, slug FROM brands ORDER BY RAND() LIMIT 1')->fetch();
        if (!$row) return null;
        return [
            'reward_type' => 'brand',
            'target_id' => (int)$row['id'],
            'target_name' => (string)$row['name'],
            'target_slug' => (string)$row['slug'],
            'discount_percent' => random_int(10, 25),
            'title' => 'Скидка на бренд ' . $row['name'],
        ];
    }

    if ($type === 'category') {
        // В колесе используем только категории из меню каталога:
        // корневые + их прямые подкатегории (без глубоких уровней вроде "Пудра/Тональные средства" и т.п.)
        $stmt = $pdo->query("
            SELECT c.id, c.name, c.slug
            FROM categories c
            WHERE (c.is_hidden IS NULL OR c.is_hidden = 0)
              AND (
                c.parent_id IS NULL
                OR c.parent_id IN (
                    SELECT r.id
                    FROM categories r
                    WHERE r.parent_id IS NULL
                      AND (r.is_hidden IS NULL OR r.is_hidden = 0)
                )
              )
            ORDER BY RAND()
            LIMIT 1
        ");
        $row = $stmt->fetch();
        if (!$row) return null;
        return [
            'reward_type' => 'category',
            'target_id' => (int)$row['id'],
            'target_name' => (string)$row['name'],
            'target_slug' => (string)$row['slug'],
            'discount_percent' => random_int(8, 20),
            'title' => 'Скидка на категорию ' . $row['name'],
        ];
    }

    $stmt = $pdo->query("SELECT p.id, p.name, p.slug FROM products p ORDER BY RAND() LIMIT 1");
    $row = $stmt->fetch();
    if (!$row) return null;
    return [
        'reward_type' => 'product',
        'target_id' => (int)$row['id'],
        'target_name' => (string)$row['name'],
        'target_slug' => (string)$row['slug'],
        'discount_percent' => random_int(15, 35),
        'title' => 'Скидка на товар ' . $row['name'],
    ];
}

function wheelPickRewardByType(PDO $pdo, string $type): ?array
{
    if ($type === 'brand') {
        $row = $pdo->query('SELECT id, name, slug FROM brands ORDER BY RAND() LIMIT 1')->fetch();
        if (!$row) return null;
        return [
            'reward_type' => 'brand',
            'target_id' => (int)$row['id'],
            'target_name' => (string)$row['name'],
            'target_slug' => (string)$row['slug'],
            'discount_percent' => random_int(10, 25),
            'title' => 'Скидка на бренд ' . $row['name'],
        ];
    }

    if ($type === 'category') {
        $stmt = $pdo->query("
            SELECT c.id, c.name, c.slug
            FROM categories c
            WHERE (c.is_hidden IS NULL OR c.is_hidden = 0)
              AND (
                c.parent_id IS NULL
                OR c.parent_id IN (
                    SELECT r.id
                    FROM categories r
                    WHERE r.parent_id IS NULL
                      AND (r.is_hidden IS NULL OR r.is_hidden = 0)
                )
              )
            ORDER BY RAND()
            LIMIT 1
        ");
        $row = $stmt->fetch();
        if (!$row) return null;
        return [
            'reward_type' => 'category',
            'target_id' => (int)$row['id'],
            'target_name' => (string)$row['name'],
            'target_slug' => (string)$row['slug'],
            'discount_percent' => random_int(8, 20),
            'title' => 'Скидка на категорию ' . $row['name'],
        ];
    }

    $row = $pdo->query("SELECT p.id, p.name, p.slug FROM products p ORDER BY RAND() LIMIT 1")->fetch();
    if (!$row) return null;
    return [
        'reward_type' => 'product',
        'target_id' => (int)$row['id'],
        'target_name' => (string)$row['name'],
        'target_slug' => (string)$row['slug'],
        'discount_percent' => random_int(15, 35),
        'title' => 'Скидка на товар ' . $row['name'],
    ];
}

function wheelBuildSectors(PDO $pdo): array
{
    $types = ['brand', 'category', 'product', 'brand', 'category', 'product', 'brand', 'category'];
    $sectors = [];
    foreach ($types as $type) {
        $reward = wheelPickRewardByType($pdo, $type);
        if (!$reward) {
            continue;
        }
        $sectors[] = $reward;
    }
    return $sectors;
}

if (isLoggedIn()) {
    wheelEnsureTables();
    $userId = (int)$_SESSION['user_id'];
    $wheelSectors = wheelBuildSectors($pdo);

    if (count($wheelSectors) === 8) {
        $wheelSectorLabels = [];
        foreach ($wheelSectors as $sector) {
            $prefix = $sector['reward_type'] === 'brand' ? 'Бренд: ' : ($sector['reward_type'] === 'category' ? 'Кат: ' : 'Товар: ');
            $wheelSectorLabels[] = $prefix . wheelShortLabel((string)$sector['target_name']);
        }
    }

    // Если пользователь был создан до внедрения колеса — выдаём бонус за регистрацию один раз.
    $stmtHasBonus = $pdo->prepare("
        SELECT 1
        FROM wheel_spin_history
        WHERE user_id = ? AND reason = 'registration_bonus'
        LIMIT 1
    ");
    $stmtHasBonus->execute([$userId]);
    if (!$stmtHasBonus->fetchColumn()) {
        wheelAddSpins($userId, 3, 'registration_bonus', null);
    }

    // Разовый бонус +15 прокруток (по запросу).
    $stmtHasManualBonus = $pdo->prepare("
        SELECT 1
        FROM wheel_spin_history
        WHERE user_id = ? AND reason = 'manual_bonus_15'
        LIMIT 1
    ");
    $stmtHasManualBonus->execute([$userId]);
    if (!$stmtHasManualBonus->fetchColumn()) {
        wheelAddSpins($userId, 15, 'manual_bonus_15', null);
    }

    // Разовый бонус +10 прокруток (дополнительно по запросу).
    $stmtHasManualBonus10 = $pdo->prepare("
        SELECT 1
        FROM wheel_spin_history
        WHERE user_id = ? AND reason = 'manual_bonus_10'
        LIMIT 1
    ");
    $stmtHasManualBonus10->execute([$userId]);
    if (!$stmtHasManualBonus10->fetchColumn()) {
        wheelAddSpins($userId, 10, 'manual_bonus_10', null);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'spin') {
        try {
            $pdo->beginTransaction();

            $stmtEnsure = $pdo->prepare('INSERT INTO user_wheel_spins (user_id, spins) VALUES (?, 0) ON DUPLICATE KEY UPDATE user_id = user_id');
            $stmtEnsure->execute([$userId]);

            $stmtTake = $pdo->prepare('UPDATE user_wheel_spins SET spins = spins - 1 WHERE user_id = ? AND spins > 0');
            $stmtTake->execute([$userId]);

            if ($stmtTake->rowCount() === 0) {
                $pdo->rollBack();
                $error = 'У вас нет доступных прокруток. Зарегистрируйтесь или оформите заказ от 1000 руб.';
            } else {
                if (count($wheelSectors) !== 8) {
                    $pdo->rollBack();
                    $error = 'Не удалось получить награду: в каталоге недостаточно данных.';
                } else {
                    $angles = [0, 45, 90, 135, 180, 225, 270, 315];
                    $spinSectorIndex = random_int(0, 7);
                    $spinSectorAngle = $angles[$spinSectorIndex];
                    $reward = $wheelSectors[$spinSectorIndex];

                    $promoCode = wheelGeneratePromoCode($pdo);
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

                    $stmtReward = $pdo->prepare('
                        INSERT INTO wheel_rewards (user_id, reward_type, target_id, target_name, discount_percent, promo_code, is_used, expires_at)
                        VALUES (?, ?, ?, ?, ?, ?, 0, ?)
                    ');
                    $stmtReward->execute([
                        $userId,
                        $reward['reward_type'],
                        $reward['target_id'],
                        $reward['target_name'],
                        $reward['discount_percent'],
                        $promoCode,
                        $expiresAt,
                    ]);

                    $stmtHist = $pdo->prepare('INSERT INTO wheel_spin_history (user_id, spin_delta, reason, order_id) VALUES (?, -1, ?, NULL)');
                    $stmtHist->execute([$userId, 'spin_used']);

                    $pdo->commit();

                    $spinResult = $reward;
                    $spinResult['promo_code'] = $promoCode;
                    $spinResult['expires_at'] = $expiresAt;

                    $spinResult['sector_index'] = $spinSectorIndex;

                    $_SESSION['wheel_last_spin'] = [
                        'reward' => $spinResult,
                        'sector_index' => $spinSectorIndex,
                        'sector_angle' => $spinSectorAngle,
                        'success' => 'Поздравляем! Вы получили новую скидку.',
                    ];

                    header('Location: ' . BASE_URL . 'guide.php?spin=1');
                    exit;
                }
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Ошибка прокрутки: ' . $e->getMessage();
        }
    }

    if (isset($_GET['spin']) && $_GET['spin'] === '1' && isset($_SESSION['wheel_last_spin'])) {
        $lastSpin = $_SESSION['wheel_last_spin'];
        unset($_SESSION['wheel_last_spin']);

        $spinResult = is_array($lastSpin['reward'] ?? null) ? $lastSpin['reward'] : null;
        $spinSectorIndex = isset($lastSpin['sector_index']) ? (int)$lastSpin['sector_index'] : null;
        $spinSectorAngle = isset($lastSpin['sector_angle']) ? (int)$lastSpin['sector_angle'] : null;
        $success = (string)($lastSpin['success'] ?? '');
    }

    $currentSpins = wheelGetSpins($userId);

    $stmtRewards = $pdo->prepare('SELECT * FROM wheel_rewards WHERE user_id = ? ORDER BY created_at DESC LIMIT 15');
    $stmtRewards->execute([(int)$_SESSION['user_id']]);
    $myRewards = $stmtRewards->fetchAll();

    $brands = $pdo->query('SELECT id, slug FROM brands')->fetchAll();
    $brandSlugMap = [];
    foreach ($brands as $b) $brandSlugMap[(int)$b['id']] = (string)$b['slug'];

    $categories = $pdo->query('SELECT id, slug FROM categories')->fetchAll();
    $categorySlugMap = [];
    foreach ($categories as $c) $categorySlugMap[(int)$c['id']] = (string)$c['slug'];

    $products = $pdo->query('SELECT id, slug FROM products')->fetchAll();
    $productSlugMap = [];
    foreach ($products as $p) $productSlugMap[(int)$p['id']] = (string)$p['slug'];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="wheel-page">
    <section class="wheel-hero">
        <div class="container">
            <h1>Колесо фортуны</h1>
            <p>Крутите колесо и получайте персональные скидки на бренды, категории и конкретные товары.</p>

            <div class="wheel-rules">
                <div class="wheel-rule">За регистрацию: <strong>+3 прокрутки</strong></div>
                <div class="wheel-rule">За заказ от 1000 руб: <strong>+1 прокрутка</strong></div>
            </div>
        </div>
    </section>

    <section class="wheel-content">
        <div class="container">
            <?php if (!isLoggedIn()): ?>
                <div class="wheel-card">
                    <h2>Нужно войти в аккаунт</h2>
                    <p>Прокрутки и ваши скидки привязаны к профилю пользователя.</p>
                    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>login.php?redirect=guide.php">Войти</a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success && !$spinResult): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <div class="wheel-layout">
                    <div class="wheel-card wheel-spin-area">
                        <div class="wheel-available">Доступно прокруток: <strong><?php echo (int)$currentSpins; ?></strong></div>

                        <div class="wheel-visual" aria-hidden="true">
                            <div class="wheel-pointer"></div>
                            <div
                                class="wheel-disc"
                                id="fortune-wheel"
                                data-winning-angle="<?php echo $spinSectorAngle !== null ? (int)$spinSectorAngle : ''; ?>"
                                data-winning-index="<?php echo $spinSectorIndex !== null ? (int)$spinSectorIndex : ''; ?>"
                            >
                                <span class="wheel-label" data-sector-index="0" style="--a:0deg;"><?php echo htmlspecialchars($wheelSectorLabels[0]); ?></span>
                                <span class="wheel-label" data-sector-index="1" style="--a:45deg;"><?php echo htmlspecialchars($wheelSectorLabels[1]); ?></span>
                                <span class="wheel-label" data-sector-index="2" style="--a:90deg;"><?php echo htmlspecialchars($wheelSectorLabels[2]); ?></span>
                                <span class="wheel-label" data-sector-index="3" style="--a:135deg;"><?php echo htmlspecialchars($wheelSectorLabels[3]); ?></span>
                                <span class="wheel-label" data-sector-index="4" style="--a:180deg;"><?php echo htmlspecialchars($wheelSectorLabels[4]); ?></span>
                                <span class="wheel-label" data-sector-index="5" style="--a:225deg;"><?php echo htmlspecialchars($wheelSectorLabels[5]); ?></span>
                                <span class="wheel-label" data-sector-index="6" style="--a:270deg;"><?php echo htmlspecialchars($wheelSectorLabels[6]); ?></span>
                                <span class="wheel-label" data-sector-index="7" style="--a:315deg;"><?php echo htmlspecialchars($wheelSectorLabels[7]); ?></span>
                                <span class="wheel-hub"></span>
                            </div>
                        </div>

                        <form method="POST" action="" class="wheel-spin-form">
                            <input type="hidden" name="action" value="spin">
                            <button class="btn btn-primary" type="submit" <?php echo $currentSpins <= 0 ? 'disabled' : ''; ?>>
                                Крутить колесо
                            </button>
                        </form>

                        <?php if ($spinResult): ?>
                            <div class="wheel-win" id="wheel-win-block" style="display: none;">
                                <h3>Вы выиграли: <?php echo (int)$spinResult['discount_percent']; ?>% </h3>
                                <p><strong>Сектор <?php echo ((int)$spinResult['sector_index']) + 1; ?></strong></p>
                                <p><?php echo htmlspecialchars($spinResult['title']); ?></p>
                                <p>Промокод: <strong><?php echo htmlspecialchars($spinResult['promo_code']); ?></strong></p>
                                <p class="wheel-muted">Действует до <?php echo date('d.m.Y H:i', strtotime($spinResult['expires_at'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="wheel-card">
                        <h2>Мои скидки</h2>
                        <div class="wheel-muted" style="margin: -6px 0 12px;">
                            Возможные выигрыши: <strong>бренд 10–25%</strong>, <strong>категория 8–20%</strong>, <strong>товар 15–35%</strong>.
                            Скидка выдаётся промокодом на 7 дней.
                        </div>
                        <?php if (empty($myRewards)): ?>
                            <p class="wheel-muted">Пока нет выигранных скидок.</p>
                        <?php else: ?>
                            <div class="wheel-rewards-list">
                                <?php foreach ($myRewards as $reward): ?>
                                    <?php
                                        $link = BASE_URL . 'catalog.php';
                                        if ($reward['reward_type'] === 'brand' && isset($brandSlugMap[(int)$reward['target_id']])) {
                                            $link = BASE_URL . 'catalog.php?brand=' . urlencode($brandSlugMap[(int)$reward['target_id']]);
                                        } elseif ($reward['reward_type'] === 'category' && isset($categorySlugMap[(int)$reward['target_id']])) {
                                            $link = BASE_URL . 'catalog.php?category=' . urlencode($categorySlugMap[(int)$reward['target_id']]);
                                        } elseif ($reward['reward_type'] === 'product' && isset($productSlugMap[(int)$reward['target_id']])) {
                                            $link = BASE_URL . 'product.php?product=' . urlencode($productSlugMap[(int)$reward['target_id']]);
                                        }
                                    ?>
                                     <div class="wheel-reward-item">
                                         <div class="wheel-reward-top">
                                             <span class="wheel-discount">-<?php echo (int)$reward['discount_percent']; ?>%</span>
                                             <span class="wheel-code"><?php echo htmlspecialchars($reward['promo_code']); ?></span>
                                         </div>
                                         <div class="wheel-target"><?php echo htmlspecialchars($reward['target_name']); ?></div>
                                         <div class="wheel-meta">Тип: <?php echo $reward['reward_type'] === 'brand' ? 'Бренд' : ($reward['reward_type'] === 'category' ? 'Категория' : 'Товар'); ?> • до <?php echo date('d.m.Y', strtotime($reward['expires_at'])); ?></div>
                                         <a class="wheel-link" href="<?php echo htmlspecialchars($link); ?>">Открыть товары</a>
                                     </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.wheel-spin-form');
    const wheel = document.getElementById('fortune-wheel');
    const winBlock = document.getElementById('wheel-win-block');
    if (!wheel) return;

    const winningAngleRaw = wheel.dataset.winningAngle;
    const winningIndexRaw = wheel.dataset.winningIndex;
    const winningAngle = winningAngleRaw === '' ? null : Number(winningAngleRaw);
    const winningIndex = winningIndexRaw === '' ? null : Number(winningIndexRaw);

    if (winningAngle !== null && !Number.isNaN(winningAngle)) {
        const finalRotation = 1080 - winningAngle;
        wheel.classList.remove('is-spinning');
        wheel.style.transition = 'none';
        wheel.style.setProperty('--wheel-rotation', '0deg');

        // Форсируем применение начального состояния, затем запускаем анимацию.
        void wheel.offsetWidth;

        requestAnimationFrame(function () {
            wheel.style.transition = 'transform 3.2s cubic-bezier(0.12, 0.76, 0.24, 1)';
            wheel.classList.add('is-spinning');
            wheel.style.setProperty('--wheel-rotation', finalRotation + 'deg');
        });

        if (winningIndex !== null && !Number.isNaN(winningIndex)) {
            const allLabels = wheel.querySelectorAll('.wheel-label');
            allLabels.forEach(function (label) { label.classList.remove('is-winning'); });

            const winLabel = wheel.querySelector('.wheel-label[data-sector-index="' + String(winningIndex) + '"]');
            if (winLabel) {
                wheel.addEventListener('transitionend', function onEnd(event) {
                    if (event.propertyName !== 'transform') return;
                    wheel.removeEventListener('transitionend', onEnd);
                    winLabel.classList.add('is-winning');
                    if (winBlock) {
                        winBlock.style.display = 'block';
                    }
                });
            } else if (winBlock) {
                wheel.addEventListener('transitionend', function onEnd(event) {
                    if (event.propertyName !== 'transform') return;
                    wheel.removeEventListener('transitionend', onEnd);
                    winBlock.style.display = 'block';
                });
            }
        } else if (winBlock) {
            wheel.addEventListener('transitionend', function onEnd(event) {
                if (event.propertyName !== 'transform') return;
                wheel.removeEventListener('transitionend', onEnd);
                winBlock.style.display = 'block';
            });
        }
    } else if (winBlock) {
        // Если это не страница после прокрутки, не показываем "прошлый" результат.
        winBlock.style.display = 'none';
    }

    if (!form) return;

    form.addEventListener('submit', function (event) {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Определяем выигрыш...';
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
