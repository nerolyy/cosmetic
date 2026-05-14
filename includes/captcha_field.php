<?php
/**
 * Виджет Google reCAPTCHA v2 (чекбокс). Скрипт подключается один раз на страницу.
 */
if (!defined('RECAPTCHA_WIDGET_SCRIPT')) {
    define('RECAPTCHA_WIDGET_SCRIPT', true);
    if (recaptcha_is_configured()) {
        echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>' . "\n";
    }
}
?>
<div class="form-group recaptcha-field">
    <?php if (!recaptcha_is_configured()): ?>
        <p class="recaptcha-missing">Укажите ключи <code>RECAPTCHA_SITE_KEY</code> и <code>RECAPTCHA_SECRET_KEY</code> в <code>config/config.php</code> (тип капчи: reCAPTCHA v2 → «Я не робот»).</p>
    <?php else: ?>
        <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <?php endif; ?>
</div>
