<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'edit') {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $description = trim($_POST['description'] ?? '');
            $discount_type = $_POST['discount_type'] ?? 'percentage';
            $discount_value = floatval($_POST['discount_value'] ?? 0);
            $min_order_amount = floatval($_POST['min_order_amount'] ?? 0);
            $max_discount = !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null;
            $max_uses = !empty($_POST['max_uses']) ? intval($_POST['max_uses']) : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $valid_from = $_POST['valid_from'] ?? date('Y-m-d H:i:s');
            $valid_until = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;
            
            if (empty($code) || $discount_value <= 0) {
                $_SESSION['error'] = 'Заполните все обязательные поля';
            } else {
                try {
                    if ($_POST['action'] === 'create') {
                        // Проверяем уникальность кода
                        $stmt_check = $pdo->prepare("SELECT id FROM promo_codes WHERE code = ?");
                        $stmt_check->execute([$code]);
                        if ($stmt_check->fetch()) {
                            $_SESSION['error'] = 'Промокод с таким кодом уже существует';
                        } else {
                            $stmt = $pdo->prepare("
                                INSERT INTO promo_codes 
                                (code, description, discount_type, discount_value, min_order_amount, max_discount, max_uses, is_active, valid_from, valid_until)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                $code, $description, $discount_type, $discount_value,
                                $min_order_amount, $max_discount, $max_uses, $is_active,
                                $valid_from, $valid_until
                            ]);
                            $_SESSION['success'] = 'Промокод успешно создан';
                        }
                    } else {
                        $id = intval($_POST['id']);
                        // Проверяем уникальность кода (кроме текущего)
                        $stmt_check = $pdo->prepare("SELECT id FROM promo_codes WHERE code = ? AND id != ?");
                        $stmt_check->execute([$code, $id]);
                        if ($stmt_check->fetch()) {
                            $_SESSION['error'] = 'Промокод с таким кодом уже существует';
                        } else {
                            $stmt = $pdo->prepare("
                                UPDATE promo_codes 
                                SET code = ?, description = ?, discount_type = ?, discount_value = ?,
                                    min_order_amount = ?, max_discount = ?, max_uses = ?, is_active = ?,
                                    valid_from = ?, valid_until = ?
                                WHERE id = ?
                            ");
                            $stmt->execute([
                                $code, $description, $discount_type, $discount_value,
                                $min_order_amount, $max_discount, $max_uses, $is_active,
                                $valid_from, $valid_until, $id
                            ]);
                            $_SESSION['success'] = 'Промокод успешно обновлен';
                        }
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Ошибка: ' . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success'] = 'Промокод удален';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Ошибка при удалении';
            }
        }
        header('Location: promo_codes.php');
        exit;
    }
}

// Получаем список промокодов
$stmt = $pdo->query("
    SELECT * FROM promo_codes 
    ORDER BY created_at DESC
");
$promo_codes = $stmt->fetchAll();

// Получаем промокод для редактирования
$edit_promo = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE id = ?");
    $stmt->execute([$id]);
    $edit_promo = $stmt->fetch();
}

$page_title = 'Промокоды';
include '../includes/header.php';
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
                <li><a href="promo_codes.php" class="admin-nav-link active">Промокоды</a></li>
                <li><a href="<?php echo BASE_URL; ?>" class="admin-nav-link">На сайт</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Промокоды</h1>
            <button onclick="document.getElementById('promo-form').style.display='block'" class="btn btn-primary">
                + Добавить промокод
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Форма добавления/редактирования -->
        <div id="promo-form" class="admin-form" style="<?php echo $edit_promo ? 'display:block;' : 'display:none;'; ?>">
            <h2><?php echo $edit_promo ? 'Редактировать промокод' : 'Добавить промокод'; ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_promo ? 'edit' : 'create'; ?>">
                <?php if ($edit_promo): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_promo['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Код промокода *</label>
                    <input type="text" name="code" value="<?php echo htmlspecialchars($edit_promo['code'] ?? ''); ?>" required 
                           style="text-transform: uppercase;" maxlength="50">
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="3"><?php echo htmlspecialchars($edit_promo['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Тип скидки *</label>
                    <select name="discount_type" required>
                        <option value="percentage" <?php echo ($edit_promo['discount_type'] ?? 'percentage') === 'percentage' ? 'selected' : ''; ?>>Процент (%)</option>
                        <option value="fixed" <?php echo ($edit_promo['discount_type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>Фиксированная сумма (Р)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Размер скидки *</label>
                    <input type="number" name="discount_value" step="0.01" min="0.01" 
                           value="<?php echo $edit_promo['discount_value'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Минимальная сумма заказа (Р)</label>
                    <input type="number" name="min_order_amount" step="0.01" min="0" 
                           value="<?php echo $edit_promo['min_order_amount'] ?? '0'; ?>"
                           placeholder="0 - без ограничений">
                    <small style="color: #666; font-size: 12px;">Промокод будет действовать только для заказов от указанной суммы</small>
                </div>
                
                <div class="form-group">
                    <label>Максимальная скидка (Р) - только для процентных</label>
                    <input type="number" name="max_discount" step="0.01" min="0" 
                           value="<?php echo $edit_promo['max_discount'] ?? ''; ?>"
                           placeholder="Оставить пустым - без ограничений">
                    <small style="color: #666; font-size: 12px;">Ограничивает максимальную сумму скидки для процентных промокодов</small>
                </div>
                
                <div class="form-group">
                    <label>Максимальное количество использований</label>
                    <input type="number" name="max_uses" min="1" 
                           value="<?php echo $edit_promo['max_uses'] ?? ''; ?>" 
                           placeholder="Неограниченно, если пусто">
                </div>
                
                <div class="form-group">
                    <label>Действует с</label>
                    <input type="datetime-local" name="valid_from" 
                           value="<?php echo $edit_promo ? date('Y-m-d\TH:i', strtotime($edit_promo['valid_from'])) : date('Y-m-d\TH:i'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Действует до</label>
                    <input type="datetime-local" name="valid_until" 
                           value="<?php echo $edit_promo && $edit_promo['valid_until'] ? date('Y-m-d\TH:i', strtotime($edit_promo['valid_until'])) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" 
                               <?php echo ($edit_promo['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        Активен
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="promo_codes.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>

        <!-- Список промокодов -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Код</th>
                        <th>Описание</th>
                        <th>Тип</th>
                        <th>Скидка</th>
                        <th>Мин. сумма</th>
                        <th>Использований</th>
                        <th>Статус</th>
                        <th>Действует</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promo_codes)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px;">Промокоды не найдены</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promo_codes as $promo): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($promo['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($promo['description'] ?: '-'); ?></td>
                                <td><?php echo $promo['discount_type'] === 'percentage' ? 'Процент' : 'Фикс.'; ?></td>
                                <td>
                                    <?php 
                                    if ($promo['discount_type'] === 'percentage') {
                                        echo $promo['discount_value'] . '%';
                                        if ($promo['max_discount']) {
                                            echo ' (макс. ' . number_format($promo['max_discount'], 0, ',', ' ') . ' Р)';
                                        }
                                    } else {
                                        echo number_format($promo['discount_value'], 0, ',', ' ') . ' Р';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $promo['min_order_amount'] > 0 ? number_format($promo['min_order_amount'], 0, ',', ' ') . ' Р' : '-'; ?></td>
                                <td>
                                    <?php 
                                    echo $promo['current_uses'];
                                    if ($promo['max_uses']) {
                                        echo ' / ' . $promo['max_uses'];
                                    } else {
                                        echo ' / ∞';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $promo['is_active'] ? 'badge-success' : 'badge-error'; ?>">
                                        <?php echo $promo['is_active'] ? 'Активен' : 'Неактивен'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $now = new DateTime();
                                    $from = new DateTime($promo['valid_from']);
                                    $until = $promo['valid_until'] ? new DateTime($promo['valid_until']) : null;
                                    
                                    if ($now < $from) {
                                        echo '<span style="color: #ff9800;">Начнется ' . $from->format('d.m.Y H:i') . '</span>';
                                    } elseif ($until && $now > $until) {
                                        echo '<span style="color: #f44336;">Истек ' . $until->format('d.m.Y H:i') . '</span>';
                                    } else {
                                        echo '<span style="color: #4caf50;">Действует</span>';
                                        if ($until) {
                                            echo ' до ' . $until->format('d.m.Y H:i');
                                        } else {
                                            echo ' (без ограничений)';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $promo['id']; ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить промокод?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $promo['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

