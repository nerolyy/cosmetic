<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/smtp_client.php';

$page_title = 'Обратная связь';
$error = '';
$success = '';

if (isset($_GET['sent']) && $_GET['sent'] === '1' && !empty($_SESSION['feedback_flash']) && is_array($_SESSION['feedback_flash'])) {
    $f = $_SESSION['feedback_flash'];
    unset($_SESSION['feedback_flash']);
    if (($f['type'] ?? '') === 'success') {
        $success = (string) ($f['message'] ?? 'Сообщение принято.');
        if (!empty($f['mail_warning'])) {
            $success .= ' ' . (string) $f['mail_warning'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!recaptcha_verify_post()) {
        $error = recaptcha_is_configured()
            ? 'Подтвердите, что вы не робот (reCAPTCHA), и попробуйте снова.'
            : 'Капча не настроена на сервере. Обратитесь к администратору.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['message'] ?? ''));

        if ($name === '' || mb_strlen($name) > 120) {
            $error = 'Укажите имя (до 120 символов).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Укажите корректный email.';
        } elseif ($subject === '' || mb_strlen($subject) > 255) {
            $error = 'Укажите тему (до 255 символов).';
        } elseif ($body === '' || mb_strlen($body) > 8000) {
            $error = 'Введите текст сообщения (до 8000 символов).';
        } else {
            feedbackEnsureTable();
            $userRow = getCurrentUser();
            $userId = $userRow ? (int) $userRow['id'] : null;
            $ip = isset($_SERVER['REMOTE_ADDR']) ? substr((string) $_SERVER['REMOTE_ADDR'], 0, 45) : null;

            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO contact_feedback (user_id, name, email, subject, body, ip) VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$userId, $name, $email, $subject, $body, $ip]);
            } catch (PDOException $e) {
                $error = 'Не удалось сохранить сообщение. Попробуйте позже.';
            }

            if ($error === '') {
                $inbox = trim((string) (defined('FEEDBACK_INBOX_EMAIL') ? FEEDBACK_INBOX_EMAIL : ''));
                if ($inbox === '') {
                    $inbox = (string) SMTP_FROM_EMAIL;
                }

                $adminText = "Новое сообщение с сайта.\n\n" .
                    "Имя: {$name}\n" .
                    "Email: {$email}\n" .
                    "Тема: {$subject}\n" .
                    ($ip ? "IP: {$ip}\n" : '') .
                    "\n---\n" .
                    $body . "\n";

                $okAdmin = app_send_plain_email($inbox, '[Косметика] Обратная связь: ' . $subject, $adminText, $email, $name);

                $userCopy = "Здравствуйте, {$name}!\n\n" .
                    "Мы получили ваше сообщение и скоро ответим.\n\n" .
                    "Копия вашего обращения:\n" .
                    "Тема: {$subject}\n\n" .
                    $body . "\n\n" .
                    "---\n" .
                    "С уважением, " . (defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Косметика') . "\n";

                $okUser = app_send_plain_email($email, 'Ваше сообщение получено: ' . $subject, $userCopy);

                $mailWarning = '';
                if (!$okAdmin || !$okUser) {
                    $mailWarning = 'Сообщение сохранено. Если письмо с копией не пришло в течение нескольких минут, проверьте папку «Спам» или напишите нам с другого адреса.';
                    if (defined('SMTP_ENABLED') && SMTP_ENABLED && !empty($_SESSION['smtp_last_error']) && is_array($_SESSION['smtp_last_error'])) {
                        $e = $_SESSION['smtp_last_error'];
                        $mailWarning .= ' (этап SMTP: ' . (string) ($e['stage'] ?? 'unknown') . ')';
                    }
                }

                $_SESSION['feedback_flash'] = [
                    'type' => 'success',
                    'message' => 'Спасибо! Сообщение отправлено. На вашу почту ушла копия обращения.',
                    'mail_warning' => $mailWarning,
                ];
                header('Location: ' . BASE_URL . 'feedback.php?sent=1', true, 303);
                exit;
            }
        }
    }
}

$cu = getCurrentUser();
$fb_name = trim((string) ($_POST['name'] ?? ''));
if ($fb_name === '' && $cu) {
    $fb_name = (string) ($cu['name'] ?? '');
}
$fb_email = trim((string) ($_POST['email'] ?? ''));
if ($fb_email === '' && $cu) {
    $fb_email = (string) ($cu['email'] ?? '');
}

include __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box" style="max-width: 520px;">
        <h1>Обратная связь</h1>
        <p class="auth-link" style="margin-top: 0; margin-bottom: 20px; text-align: left;">
            Напишите нам — ответ придёт на указанный email. После отправки вы получите копию сообщения.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="fb_name">Имя *</label>
                <input type="text" id="fb_name" name="name" required maxlength="120" value="<?php echo htmlspecialchars($fb_name); ?>">
            </div>
            <div class="form-group">
                <label for="fb_email">Email *</label>
                <input type="email" id="fb_email" name="email" required value="<?php echo htmlspecialchars($fb_email); ?>">
            </div>
            <div class="form-group">
                <label for="fb_subject">Тема *</label>
                <input type="text" id="fb_subject" name="subject" required maxlength="255" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="fb_message">Сообщение *</label>
                <textarea id="fb_message" name="message" required rows="8" maxlength="8000" style="width: 100%; box-sizing: border-box; padding: 10px; font-family: inherit;"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>
            <?php include __DIR__ . '/../includes/captcha_field.php'; ?>
            <button type="submit" class="btn btn-primary">Отправить</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
