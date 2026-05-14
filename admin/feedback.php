<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

feedbackEnsureTable();

/**
 * Ссылка mailto: с темой Re: и цитатой (длина ограничена для совместимости с клиентами).
 */
function admin_feedback_mailto_reply_url(array $row): string
{
    $to = (string) ($row['email'] ?? '');
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return '#';
    }
    $name = (string) ($row['name'] ?? '');
    $subject = (string) ($row['subject'] ?? '');
    $created = (string) ($row['created_at'] ?? '');
    $body = (string) ($row['body'] ?? '');

    $sub = 'Re: ' . $subject;
    $replyBody = "Здравствуйте" . ($name !== '' ? ', ' . $name : '') . "!\n\n\n\n--- Исходное сообщение (" . $created . ") ---\n" . $body;
    if (strlen($replyBody) > 2000) {
        $replyBody = substr($replyBody, 0, 1997) . "…";
    }

    return 'mailto:' . $to . '?subject=' . rawurlencode($sub) . '&body=' . rawurlencode($replyBody);
}

$viewId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$viewRow = null;
if ($viewId > 0) {
    $st = $pdo->prepare('SELECT * FROM contact_feedback WHERE id = ? LIMIT 1');
    $st->execute([$viewId]);
    $viewRow = $st->fetch();
}

$stmt = $pdo->query('SELECT id, name, email, subject, created_at FROM contact_feedback ORDER BY created_at DESC LIMIT 200');
$rows = $stmt->fetchAll();

$page_title = 'Обратная связь';
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h2>Админ-панель</h2>
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php" class="admin-nav-link">Главная</a></li>
                <li><a href="products.php" class="admin-nav-link">Товары</a></li>
                <li><a href="categories.php" class="admin-nav-link">Категории</a></li>
                <li><a href="brands.php" class="admin-nav-link">Бренды</a></li>
                <li><a href="orders.php" class="admin-nav-link">Заказы</a></li>
                <li><a href="users.php" class="admin-nav-link">Пользователи</a></li>
                <li><a href="shops.php" class="admin-nav-link">Магазины</a></li>
                <li><a href="promo_codes.php" class="admin-nav-link">Промокоды</a></li>
                <li><a href="feedback.php" class="admin-nav-link active">Обратная связь</a></li>
                <li><a href="seed_catalog.php" class="admin-nav-link">Seed каталога</a></li>
                <li><a href="<?php echo BASE_URL; ?>" class="admin-nav-link">На сайт</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Сообщения с сайта</h1>
        </div>

        <?php if ($viewRow): ?>
            <p style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <a href="feedback.php" class="btn btn-secondary">← К списку</a>
                <a href="<?php echo htmlspecialchars(admin_feedback_mailto_reply_url($viewRow), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">Ответить по почте</a>
            </p>
            <p class="form-hint" style="margin: 8px 0 16px;">Если вы отвечаете из письма-уведомления на почте, кнопка «Ответить» там уже ведёт на адрес клиента (Reply-To).</p>
            <div class="admin-form" style="margin-top: 12px;">
                <p><strong>Дата:</strong> <?php echo htmlspecialchars((string) $viewRow['created_at']); ?></p>
                <p><strong>Имя:</strong> <?php echo htmlspecialchars((string) $viewRow['name']); ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars((string) $viewRow['email']); ?>"><?php echo htmlspecialchars((string) $viewRow['email']); ?></a></p>
                <p><strong>Тема:</strong> <?php echo htmlspecialchars((string) $viewRow['subject']); ?></p>
                <?php if (!empty($viewRow['ip'])): ?>
                    <p><strong>IP:</strong> <?php echo htmlspecialchars((string) $viewRow['ip']); ?></p>
                <?php endif; ?>
                <p><strong>Текст:</strong></p>
                <pre style="white-space: pre-wrap; background: #f8f8f8; padding: 12px; border-radius: 6px; max-height: 400px; overflow: auto;"><?php echo htmlspecialchars((string) $viewRow['body']); ?></pre>
            </div>
        <?php else: ?>
            <?php if (empty($rows)): ?>
                <p>Пока нет сообщений.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Дата</th>
                                <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Имя</th>
                                <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Email</th>
                                <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Тема</th>
                                <th style="padding: 8px; border-bottom: 1px solid #ddd;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; white-space: nowrap;"><?php echo htmlspecialchars((string) $r['created_at']); ?></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string) $r['name']); ?></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string) $r['email']); ?></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars((string) $r['subject']); ?></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><a href="feedback.php?id=<?php echo (int) $r['id']; ?>">Открыть</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
