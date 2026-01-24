# 📁 Структура проекта

Проект организован по модульному принципу для удобной навигации и поддержки.

## 📂 Структура папок

```
cosmetic/
├── admin/              # Админ-панель
│   ├── brands.php
│   ├── categories.php
│   ├── index.php
│   ├── orders.php
│   ├── products.php
│   ├── promo_codes.php
│   ├── shops.php
│   └── users.php
│
├── api/                # API endpoints (AJAX запросы)
│   ├── brand_favorite.php
│   ├── cart_api.php
│   ├── check_promo_code.php
│   ├── create_order.php
│   ├── delete_address.php
│   ├── product_favorite.php
│   ├── save_address.php
│   └── update_profile.php
│
├── assets/             # Статические файлы
│   ├── css/           # Стили
│   │   ├── base.css
│   │   ├── components.css
│   │   ├── layout.css
│   │   ├── pages.css
│   │   └── style.css
│   ├── js/            # JavaScript
│   │   ├── cart.js
│   │   ├── favorites.js
│   │   └── homepage.js
│   └── img/           # Изображения
│
├── config/             # Конфигурация
│   └── config.php      # Главный конфиг (БД, константы, функции)
│
├── includes/           # Общие компоненты
│   ├── header.php      # Шапка сайта
│   └── footer.php     # Подвал сайта
│
├── pages/              # Публичные страницы
│   ├── index.php       # Главная страница
│   ├── catalog.php     # Каталог товаров
│   ├── brands.php      # Страница брендов
│   ├── shops.php       # Страница магазинов
│   ├── cart.php        # Корзина
│   ├── profile.php     # Профиль пользователя
│   ├── login.php       # Вход
│   ├── register.php    # Регистрация
│   └── logout.php      # Выход
│
├── uploads/            # Загруженные файлы (изображения товаров)
│
├── index.php           # Роутер (обрабатывает все запросы к страницам)
├── install.php         # Скрипт установки БД
├── data.sql.example    # Пример файла данных для импорта
├── .htaccess           # Правила маршрутизации (опционально)
├── README_INSTALL.md   # Инструкция по установке
└── README_STRUCTURE.md # Документация по структуре проекта
```

## 🔗 Пути и константы

### Константы (определены в `config/config.php`):

- `BASE_URL` - базовый URL сайта
- `ROOT_PATH` - абсолютный путь к корню проекта
- `ASSETS_URL` - URL к папке assets
- `ASSETS_PATH` - путь к папке assets
- `UPLOADS_URL` - URL к папке uploads
- `UPLOADS_PATH` - путь к папке uploads

### Использование путей:

**В PHP файлах:**
```php
// Подключение config
require_once __DIR__ . '/../config/config.php';

// Подключение includes
include __DIR__ . '/../includes/header.php';

// Использование констант
<img src="<?php echo UPLOADS_URL . $image; ?>">
<link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
```

**В JavaScript:**
```javascript
// API запросы
fetch('api/cart_api.php', { ... })
fetch('api/check_promo_code.php', { ... })
```

## 📝 Правила организации

1. **API endpoints** (`api/`) - только обработка AJAX запросов, без HTML
2. **Pages** (`pages/`) - публичные страницы с HTML разметкой
3. **Admin** (`admin/`) - страницы админ-панели
4. **Assets** (`assets/`) - все статические файлы (CSS, JS, изображения)
5. **Config** (`config/`) - конфигурационные файлы
6. **Includes** (`includes/`) - переиспользуемые компоненты (header, footer)

## 🔄 Маршрутизация

Все запросы к страницам обрабатываются единым роутером в `index.php`:
- `/cosmetic/profile.php` → `pages/profile.php`
- `/cosmetic/catalog.php` → `pages/catalog.php`
- `/cosmetic/` → `pages/index.php`

Роутер автоматически определяет запрашиваемую страницу и подключает соответствующий файл из папки `pages/`. Это позволяет:
- Использовать старые URL без изменений
- Сохранить все ссылки в коде
- Иметь единую точку входа для всех страниц

## 🚀 Преимущества структуры

- ✅ Логическая организация файлов
- ✅ Легкая навигация по проекту
- ✅ Простое добавление новых модулей
- ✅ Разделение ответственности (API отдельно от страниц)
- ✅ Централизованная конфигурация
- ✅ Обратная совместимость через файлы-алиасы

