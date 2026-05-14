    </main>

    <footer class="main-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-content">
                    <!-- Логотип и название -->
                    <div class="footer-column footer-about">
                        <div class="footer-logo">
                            <div class="footer-logo-icon">
                                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                </svg>
                            </div>
                            <span class="footer-logo-text">Косметика</span>
                        </div>
                        <p class="footer-description">
                            Интернет-магазин качественной косметики и парфюмерии. Широкий ассортимент оригинальной продукции от ведущих мировых брендов.
                        </p>
                    </div>

                    <!-- Навигация -->
                    <div class="footer-column">
                        <h3 class="footer-title">Навигация</h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo BASE_URL; ?>">Главная</a></li>
                            <li><a href="<?php echo BASE_URL; ?>catalog.php">Каталог</a></li>
                            <li><a href="<?php echo BASE_URL; ?>brands.php">Бренды</a></li>
                            <li><a href="<?php echo BASE_URL; ?>shops.php">Магазины</a></li>
                            <li><a href="<?php echo BASE_URL; ?>feedback.php">Обратная связь</a></li>
                        </ul>
                    </div>

                    <!-- Покупателям -->
                    <div class="footer-column">
                        <h3 class="footer-title">Покупателям</h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo BASE_URL; ?>cart.php">Корзина</a></li>
                            <?php if (isLoggedIn()): ?>
                                <li><a href="<?php echo BASE_URL; ?>profile.php">Профиль</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo BASE_URL; ?>login.php">Войти</a></li>
                                <li><a href="<?php echo BASE_URL; ?>register.php">Регистрация</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <p class="footer-copyright">
                        <span class="copyright-icon">&copy;</span>
                        <?php echo date('Y'); ?> <strong>Косметика</strong>. Все права защищены.
                    </p>
                </div>
            </div>
            <div class="footer-decoration">
                <div class="footer-decoration-circle"></div>
                <div class="footer-decoration-circle"></div>
                <div class="footer-decoration-circle"></div>
            </div>
        </div>
    </footer>

</body>
</html>



