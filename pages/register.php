<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/smtp_client.php';

$error = '';
$success = '';

// Сессия для 2‑шаговой регистрации
$pending = $_SESSION['register_pending'] ?? null;

function register_clear_pending(): void
{
    unset($_SESSION['register_pending']);
}

function register_send_code_email(string $toEmail, string $code): bool
{
    $subject = 'Код подтверждения регистрации';
    $message = "Ваш код подтверждения: {$code}\n\n" .
        "Код действует ограниченное время. Если вы не регистрировались — просто игнорируйте это письмо.\n";

    return app_send_plain_email($toEmail, $subject, $message);
}

function register_make_code(): string
{
    return (string)random_int(100000, 999999);
}

function register_hash_code(string $code, string $secret): string
{
    return hash('sha256', $code . '|' . $secret);
}

if (isset($_GET['reset'])) {
    register_clear_pending();
    header('Location: register.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'start' || $action === '') {
        if (!recaptcha_verify_post()) {
            $error = recaptcha_is_configured()
                ? 'Подтвердите, что вы не робот (reCAPTCHA), и попробуйте снова.' . recaptcha_failure_hint_for_user()
                : 'Капча не настроена на сервере. Обратитесь к администратору.';
        } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Заполните все обязательные поля';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Неверный формат email';
        } elseif (strlen($password) < 6) {
            $error = 'Пароль должен быть не менее 6 символов';
        } elseif ($password !== $password_confirm) {
            $error = 'Пароли не совпадают';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким email уже существует';
            } else {
                $code = register_make_code();
                $secret = bin2hex(random_bytes(16));

                $sent = register_send_code_email($email, $code);
                if (!$sent) {
                    $details = '';
                    if (defined('SMTP_ENABLED') && SMTP_ENABLED && !empty($_SESSION['smtp_last_error']) && is_array($_SESSION['smtp_last_error'])) {
                        $e = $_SESSION['smtp_last_error'];
                        $details = ' SMTP: ' . htmlspecialchars(($e['stage'] ?? 'unknown')) . ' @ ' . htmlspecialchars(($e['remote'] ?? '')) . '.';
                        if (!empty($e['error'])) $details .= ' ' . htmlspecialchars((string)$e['error']);
                        if (!empty($e['errno'])) $details .= ' (errno ' . htmlspecialchars((string)$e['errno']) . ')';
                        if (!empty($e['server'])) $details .= ' Ответ сервера: ' . htmlspecialchars((string)$e['server']);
                    }
                    $error = 'Не удалось отправить письмо с кодом. Проверьте SMTP-настройки.' . $details;
                } else {
                    $_SESSION['register_pending'] = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        // роль намеренно фиксируем как user (публичная регистрация)
                        'role' => 'user',
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'code_hash' => register_hash_code($code, $secret),
                        'code_secret' => $secret,
                        'expires_at' => time() + 10 * 60, // 10 минут
                        'attempts_left' => 5,
                        'resend_after' => time() + 30, // 30 секунд cooldown
                    ];
                    $pending = $_SESSION['register_pending'];
                    $success = 'Мы отправили код на вашу почту. Введите его ниже, чтобы завершить регистрацию.';
                }
            }
        }
        }
    } elseif ($action === 'verify') {
        $codeRaw = trim($_POST['code'] ?? '');
        // Нормализуем ввод: иногда код вставляют с пробелами/дефисами.
        $code = preg_replace('/\D+/', '', $codeRaw);
        $pending = $_SESSION['register_pending'] ?? null;

        if (!$pending || empty($pending['email'])) {
            $error = 'Сессия подтверждения истекла. Начните регистрацию заново.';
            register_clear_pending();
        } elseif (time() > (int)$pending['expires_at']) {
            $error = 'Код истёк. Отправьте код ещё раз.';
        } elseif (empty($code) || strlen($code) !== 6) {
            $error = 'Введите 6‑значный код из письма';
        } else {
            $attemptsLeft = (int)($pending['attempts_left'] ?? 0);
            if ($attemptsLeft <= 0) {
                $error = 'Слишком много попыток. Отправьте код ещё раз.';
            } else {
                $calc = register_hash_code($code, (string)$pending['code_secret']);
                $ok = hash_equals((string)$pending['code_hash'], $calc);

                if (!$ok) {
                    $_SESSION['register_pending']['attempts_left'] = $attemptsLeft - 1;
                    $pending = $_SESSION['register_pending'];
                    $error = 'Неверный код. Попробуйте ещё раз.';
                } else {
                    // Ещё раз проверяем, что email не заняли параллельно
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$pending['email']]);
                    if ($stmt->fetch()) {
                        $error = 'Пользователь с таким email уже существует';
                        register_clear_pending();
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
                        $okInsert = $stmt->execute([
                            $pending['name'],
                            $pending['email'],
                            $pending['phone'],
                            $pending['password_hash'],
                            'user',
                        ]);

                        if ($okInsert) {
                            $newUserId = (int)$pdo->lastInsertId();
                            if ($newUserId > 0) {
                                // Бонус за регистрацию: 3 прокрутки колеса.
                                wheelAddSpins($newUserId, 3, 'registration_bonus', null);
                            }
                            register_clear_pending();
                            header('Location: login.php?success=1');
                            exit;
                        }
                        $error = 'Ошибка при регистрации';
                    }
                }
            }
        }
    } elseif ($action === 'resend') {
        $pending = $_SESSION['register_pending'] ?? null;

        if (!$pending || empty($pending['email'])) {
            $error = 'Сессия подтверждения истекла. Начните регистрацию заново.';
            register_clear_pending();
        } else {
            $resendAfter = (int)($pending['resend_after'] ?? 0);
            if (time() < $resendAfter) {
                $error = 'Подождите немного перед повторной отправкой кода.';
            } else {
                $code = register_make_code();
                $secret = bin2hex(random_bytes(16));
                $sent = register_send_code_email((string)$pending['email'], $code);
                if (!$sent) {
                    $details = '';
                    if (defined('SMTP_ENABLED') && SMTP_ENABLED && !empty($_SESSION['smtp_last_error']) && is_array($_SESSION['smtp_last_error'])) {
                        $e = $_SESSION['smtp_last_error'];
                        $details = ' SMTP: ' . htmlspecialchars(($e['stage'] ?? 'unknown')) . ' @ ' . htmlspecialchars(($e['remote'] ?? '')) . '.';
                        if (!empty($e['error'])) $details .= ' ' . htmlspecialchars((string)$e['error']);
                        if (!empty($e['errno'])) $details .= ' (errno ' . htmlspecialchars((string)$e['errno']) . ')';
                        if (!empty($e['server'])) $details .= ' Ответ сервера: ' . htmlspecialchars((string)$e['server']);
                    }
                    $error = 'Не удалось отправить письмо с кодом. Проверьте SMTP-настройки.' . $details;
                } else {
                    $_SESSION['register_pending']['code_hash'] = register_hash_code($code, $secret);
                    $_SESSION['register_pending']['code_secret'] = $secret;
                    $_SESSION['register_pending']['expires_at'] = time() + 10 * 60;
                    $_SESSION['register_pending']['attempts_left'] = 5;
                    $_SESSION['register_pending']['resend_after'] = time() + 30;
                    $pending = $_SESSION['register_pending'];
                    $success = 'Код отправлен повторно. Проверьте почту.';
                }
            }
        }
    }
}

$page_title = 'Регистрация';
include __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Регистрация</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!$pending): ?>
            <form method="POST" action="">
                <input type="hidden" name="action" value="start">
                <div class="form-group">
                    <label for="name">Имя *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Пароль *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Подтвердите пароль *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                </div>

                <?php include __DIR__ . '/../includes/captcha_field.php'; ?>

                <button type="submit" class="btn btn-primary">Получить код на почту</button>
            </form>
        <?php else: ?>
            <div style="margin-bottom: 12px; color: var(--text-light); font-size: 14px;">
                Код отправлен на: <strong><?php echo htmlspecialchars($pending['email']); ?></strong>
                <?php if (!empty($pending['expires_at'])): ?>
                    <?php $minutesLeft = max(0, (int)ceil(((int)$pending['expires_at'] - time()) / 60)); ?>
                    <span style="margin-left: 10px;">(осталось примерно <?php echo $minutesLeft; ?> мин.)</span>
                <?php endif; ?>
            </div>
            <div style="margin-bottom: 10px; color: var(--text-light); font-size: 13px;">
                Если почта указана неверно — нажмите «Начать заново» и введите email заново.
            </div>

            <form method="POST" action="">
                <input type="hidden" name="action" value="verify">
                <div class="form-group">
                    <label for="code">Код из письма *</label>
                    <input type="text" id="code" name="code" inputmode="numeric" maxlength="12" placeholder="123456" autocomplete="one-time-code" required>
                    <small style="display:block; margin-top:6px; color: var(--text-light);">
                        Осталось попыток: <?php echo (int)($pending['attempts_left'] ?? 0); ?>
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">Подтвердить и создать аккаунт</button>
            </form>

            <div class="register-verify-extras">
                <form method="POST" action="" class="register-resend-form">
                    <input type="hidden" name="action" value="resend">
                    <button type="submit" class="btn btn-secondary">Отправить код ещё раз</button>
                </form>
                <a class="btn btn-secondary register-reset-link" href="register.php?reset=1">Начать заново</a>
            </div>
        <?php endif; ?>

        <p class="auth-link">
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>



