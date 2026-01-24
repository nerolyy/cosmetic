<?php
require_once __DIR__ . '/../config/config.php';

$page_title = 'Магазины';

// Получаем все магазины из БД
$stmt = $pdo->query("SELECT * FROM shops ORDER BY name");
$shops = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="shops-page">
    <div class="container">
        <h1 class="page-title">Наши магазины</h1>
        
        <div class="shops-layout">
            <!-- Карта -->
            <div class="shops-map-container">
                <div id="map" style="width: 100%; height: 600px;"></div>
            </div>
            
            <!-- Список магазинов -->
            <div class="shops-list">
                <?php if (empty($shops)): ?>
                    <p>Магазины пока не добавлены.</p>
                <?php else: ?>
                    <?php foreach ($shops as $shop): ?>
                        <div class="shop-card" data-shop-id="<?php echo $shop['id']; ?>" 
                             data-lat="<?php echo $shop['latitude']; ?>" 
                             data-lng="<?php echo $shop['longitude']; ?>">
                            <h3 class="shop-name"><?php echo htmlspecialchars($shop['name']); ?></h3>
                            
                            <?php if (!empty($shop['address'])): ?>
                                <div class="shop-info">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span class="shop-address"><?php echo htmlspecialchars($shop['address']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($shop['phone'])): ?>
                                <div class="shop-info">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    <a href="tel:<?php echo htmlspecialchars($shop['phone']); ?>" class="shop-phone">
                                        <?php echo htmlspecialchars($shop['phone']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($shop['work_hours'])): ?>
                                <div class="shop-info">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span class="shop-hours"><?php echo htmlspecialchars($shop['work_hours']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($shop['description'])): ?>
                                <p class="shop-description"><?php echo htmlspecialchars($shop['description']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($shop['how_to_get'])): ?>
                                <div class="shop-how-to-get">
                                    <strong>Как добраться:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($shop['how_to_get'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Яндекс.Карты API -->
<script src="https://api-maps.yandex.ru/2.1/?apikey=YOUR_API_KEY&lang=ru_RU" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Данные магазинов из PHP
    const shops = <?php echo json_encode($shops, JSON_UNESCAPED_UNICODE); ?>;
    
    if (shops.length === 0) {
        return;
    }
    
    // Инициализация карты
    ymaps.ready(function () {
        // Создаем карту
        const map = new ymaps.Map('map', {
            center: [shops[0].latitude, shops[0].longitude], // Центр на первом магазине
            zoom: 11,
            controls: ['zoomControl', 'fullscreenControl']
        });
        
        // Массив для группировки меток
        const placemarks = [];
        
            // Создаем метки для каждого магазина
            shops.forEach(function(shop, index) {
                const placemark = new ymaps.Placemark(
                    [shop.latitude, shop.longitude],
                    {
                        balloonContentHeader: '<strong>' + shop.name + '</strong>',
                        balloonContentBody: 
                            (shop.address ? '<p><strong>Адрес:</strong> ' + shop.address + '</p>' : '') +
                            (shop.phone ? '<p><strong>Телефон:</strong> ' + shop.phone + '</p>' : '') +
                            (shop.work_hours ? '<p><strong>Режим работы:</strong> ' + shop.work_hours + '</p>' : '') +
                            (shop.how_to_get ? '<p><strong>Как добраться:</strong> ' + shop.how_to_get + '</p>' : ''),
                        balloonContentFooter: '',
                        hintContent: shop.name
                    },
                    {
                        preset: 'islands#pinkDotIcon'
                    }
                );
                
                placemarks.push(placemark);
                map.geoObjects.add(placemark);
                
                // Добавляем обработчик клика на карточку магазина
                const shopCard = document.querySelector('.shop-card[data-shop-id="' + shop.id + '"]');
                if (shopCard) {
                    shopCard.addEventListener('click', function(e) {
                        // Убираем выделение с других карточек
                        document.querySelectorAll('.shop-card').forEach(function(card) {
                            card.classList.remove('active');
                        });
                        
                        // Выделяем текущую карточку
                        shopCard.classList.add('active');
                        
                        // Плавно перемещаем карту к выбранному магазину
                        map.setCenter([shop.latitude, shop.longitude], 16, {
                            duration: 500
                        }).then(function() {
                            // Открываем балун после завершения анимации
                            placemark.balloon.open();
                        });
                        
                        // Подсвечиваем метку (делаем её больше на короткое время)
                        placemark.options.set('preset', 'islands#pinkStretchyIcon');
                        setTimeout(function() {
                            placemark.options.set('preset', 'islands#pinkDotIcon');
                        }, 1000);
                    });
                }
                
                // Обработчик клика на метку на карте - выделяем карточку
                placemark.events.add('click', function() {
                    document.querySelectorAll('.shop-card').forEach(function(card) {
                        card.classList.remove('active');
                    });
                    if (shopCard) {
                        shopCard.classList.add('active');
                        // Прокручиваем к карточке, если она не видна
                        shopCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });
        
        // Если меток больше одной, подгоняем масштаб
        if (placemarks.length > 1) {
            map.setBounds(map.geoObjects.getBounds());
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

