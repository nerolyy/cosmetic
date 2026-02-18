-- Файл для восстановления данных базы данных cosmetic_shop
-- Этот файл содержит товары, промокоды, бренды, категории и другие данные

-- ========== БРЕНДЫ ==========
INSERT INTO brands (name, slug) VALUES
('Dior', 'dior'),
('Chanel', 'chanel'),
('Yves Saint Laurent', 'ysl'),
('MAC', 'mac'),
('L\'Oreal', 'loreal'),
('Maybelline', 'maybelline'),
('Estee Lauder', 'estee-lauder'),
('Clinique', 'clinique'),
('Lancome', 'lancome'),
('NARS', 'nars'),
('Urban Decay', 'urban-decay'),
('Too Faced', 'too-faced')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- ========== КАТЕГОРИИ ==========
-- Основные категории (parent_id = NULL)
INSERT INTO categories (name, slug, parent_id, is_hidden) VALUES
('Макияж', 'makiyazh', NULL, 0),
('Парфюмерия', 'parfyumeriya', NULL, 0),
('Волосы', 'volosy', NULL, 0),
('Маникюр', 'manikyur', NULL, 0),
('Мужчины', 'muzhchiny', NULL, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Макияж - Лицо, Глаза, Губы
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Лицо', 'litso', id, 0 FROM categories WHERE slug = 'makiyazh' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Глаза', 'glaza', id, 0 FROM categories WHERE slug = 'makiyazh' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Губы', 'guby', id, 0 FROM categories WHERE slug = 'makiyazh' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Лицо - Тональные средства, Пудра, Румяна
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Тональные средства', 'tonalnye-sredstva', id, 0 FROM categories WHERE slug = 'litso' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Пудра', 'pudra', id, 0 FROM categories WHERE slug = 'litso' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Румяна', 'rumyana', id, 0 FROM categories WHERE slug = 'litso' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Глаза - Подводки, Туши, Тени для ресниц
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Подводки', 'podvodki', id, 0 FROM categories WHERE slug = 'glaza' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Туши', 'tushi', id, 0 FROM categories WHERE slug = 'glaza' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Тени для ресниц', 'teni-dlya-resnits', id, 0 FROM categories WHERE slug = 'glaza' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Губы - Губные помады, Блески для губ, Гигиенические помады
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Губные помады', 'gubnye-pomady', id, 0 FROM categories WHERE slug = 'guby' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Блески для губ', 'bleski-dlya-gub', id, 0 FROM categories WHERE slug = 'guby' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Гигиенические помады', 'gigienicheskie-pomady', id, 0 FROM categories WHERE slug = 'guby' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Парфюмерия - Женские, Мужские, Унисекс ароматы
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Женские ароматы', 'zhenskie-aromaty', id, 0 FROM categories WHERE slug = 'parfyumeriya' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Мужские ароматы', 'muzhskie-aromaty', id, 0 FROM categories WHERE slug = 'parfyumeriya' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Унисекс ароматы', 'uniseks-aromaty', id, 0 FROM categories WHERE slug = 'parfyumeriya' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Волосы - Сухие шампуни, Кондиционеры и бальзамы, Стайлинг
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Сухие шампуни', 'suhie-shampuni', id, 0 FROM categories WHERE slug = 'volosy' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Кондиционеры и бальзамы', 'konditsionery-i-balzamy', id, 0 FROM categories WHERE slug = 'volosy' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Стайлинг', 'stayling', id, 0 FROM categories WHERE slug = 'volosy' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Стайлинг - Термозащита, Фиксация, Придание объема
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Термозащита', 'termozashchita', id, 0 FROM categories WHERE slug = 'stayling' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Фиксация', 'fiksatsiya', id, 0 FROM categories WHERE slug = 'stayling' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Придание объема', 'pridanie-obema', id, 0 FROM categories WHERE slug = 'stayling' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Маникюр - Средства для снятия лака, Гель лаки, Лаки для ногтей
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Средства для снятия лака', 'sredstva-dlya-snyatiya-laka', id, 0 FROM categories WHERE slug = 'manikyur' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Гель лаки', 'gel-laki', id, 0 FROM categories WHERE slug = 'manikyur' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Лаки для ногтей', 'laki-dlya-nogtey', id, 0 FROM categories WHERE slug = 'manikyur' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Мужчины - Волосы, Бритье, Уход
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Волосы', 'volosy-muzhchiny', id, 0 FROM categories WHERE slug = 'muzhchiny' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Бритье', 'britie', id, 0 FROM categories WHERE slug = 'muzhchiny' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Уход', 'uhod-muzhchiny', id, 0 FROM categories WHERE slug = 'muzhchiny' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Мужчины - Волосы - Шампунь, Кондиционер
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Шампунь', 'shampun-muzhchiny', id, 0 FROM categories WHERE slug = 'volosy-muzhchiny' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Кондиционер', 'konditsioner-muzhchiny', id, 0 FROM categories WHERE slug = 'volosy-muzhchiny' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Мужчины - Бритье - Триммеры и станки, Средства для бритья, Средства после бритья
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Триммеры и станки', 'trimmery-i-stanki', id, 0 FROM categories WHERE slug = 'britie' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Средства для бритья', 'sredstva-dlya-britya', id, 0 FROM categories WHERE slug = 'britie' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Средства после бритья', 'sredstva-posle-britya', id, 0 FROM categories WHERE slug = 'britie' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Подкатегории Мужчины - Уход - Для душа и ванны, Дезодоранты
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Для душа и ванны', 'dlya-dusha-i-vanny', id, 0 FROM categories WHERE slug = 'uhod-muzhchiny' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO categories (name, slug, parent_id, is_hidden)
SELECT 'Дезодоранты', 'dezodoranty', id, 0 FROM categories WHERE slug = 'uhod-muzhchiny' LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- ========== ТОВАРЫ ==========
-- Удаляем все существующие товары перед добавлением новых
DELETE FROM products;

-- Макияж - Лицо - Тональные средства (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Тональный крем Yves Saint Laurent All Hours Foundation', 'ysl-all-hours-foundation', 'Долговременный тональный крем с матовым финишем. Стойкость до 24 часов, полное покрытие без ощущения маски. Подходит для всех типов кожи.', 3200.00, 3800.00, 16, (SELECT id FROM categories WHERE slug = 'tonalnye-sredstva' LIMIT 1), 3, 8, 1, 1),
('Тональный крем Dior Forever Skin Glow', 'dior-forever-skin-glow', 'Тональный крем с естественным сияющим покрытием. Подходит для всех типов кожи, стойкость до 16 часов. Содержит SPF 35.', 3500.00, 4200.00, 17, (SELECT id FROM categories WHERE slug = 'tonalnye-sredstva' LIMIT 1), 1, 7, 1, 1),
('Тональный крем Estee Lauder Double Wear Stay-in-Place', 'estee-lauder-double-wear', 'Легендарный тональный крем с матовым финишем. Стойкость до 15 часов, водостойкая формула. 55 оттенков.', 2800.00, 3300.00, 15, (SELECT id FROM categories WHERE slug = 'tonalnye-sredstva' LIMIT 1), 7, 12, 0, 1),
('Тональный крем MAC Studio Fix Fluid', 'mac-studio-fix-fluid', 'Тональный крем с матовым финишем и средней степенью покрытия. Подходит для комбинированной и жирной кожи. Широкая палитра оттенков.', 2400.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'tonalnye-sredstva' LIMIT 1), 4, 15, 0, 1),
('Тональный крем Lancome Teint Idole Ultra Wear', 'lancome-teint-idole', 'Легкий тональный крем с естественным покрытием. Стойкость до 24 часов, не забивает поры. Подходит для чувствительной кожи.', 3100.00, 3700.00, 16, (SELECT id FROM categories WHERE slug = 'tonalnye-sredstva' LIMIT 1), 9, 10, 1, 1),
('Тональный крем NARS Sheer Glow Foundation', 'nars-sheer-glow', 'Тональный крем с естественным сияющим финишем. Легкая текстура, среднее покрытие. Подходит для сухой и нормальной кожи.', 2900.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'tonalnye-sredstva' LIMIT 1), 10, 9, 0, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Макияж - Лицо - Пудра (реальные товары)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Пудра MAC Studio Fix Powder Plus Foundation', 'mac-studio-fix-powder', 'Компактная пудра для лица с естественным матовым финишем. Подходит для всех типов кожи. Среднее покрытие, стойкость до 8 часов.', 2200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'pudra' LIMIT 1), 4, 12, 0, 1),
('Пудра Chanel Les Beiges Healthy Glow', 'chanel-les-beiges-powder', 'Пудра для лица с естественным финишем. Легкая текстура, не забивает поры. Придает здоровое сияние коже.', 3200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'pudra' LIMIT 1), 2, 8, 0, 0),
('Пудра Dior Forever Perfect Cover', 'dior-forever-powder', 'Компактная пудра с матовым финишем. Стойкость до 16 часов, не забивает поры. Подходит для комбинированной кожи.', 2800.00, 3300.00, 15, (SELECT id FROM categories WHERE slug = 'pudra' LIMIT 1), 1, 10, 1, 1),
('Пудра Estee Lauder Double Wear Stay-in-Place', 'estee-lauder-double-wear-powder', 'Пудра с матовым финишем и длительной стойкостью. Водостойкая формула, идеальна для жирной кожи.', 2600.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'pudra' LIMIT 1), 7, 11, 0, 1),
('Пудра NARS Light Reflecting Pressed Setting Powder', 'nars-light-reflecting-powder', 'Пудра с эффектом светоотражения. Размывает недостатки, придает естественное сияние. Подходит для всех типов кожи.', 2700.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'pudra' LIMIT 1), 10, 9, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Макияж - Лицо - Румяна (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Румяна Chanel Joues Contraste', 'chanel-joues-contraste', 'Нежные румяна с шелковистой текстурой. Легко растушевываются, создают естественный румянец. Классические оттенки.', 2800.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'rumyana' LIMIT 1), 2, 6, 0, 0),
('Румяна NARS Orgasm', 'nars-orgasm', 'Культовая палетка румян с перламутровым отливом. Подходит для всех оттенков кожи. Естественный розовый оттенок с золотым блеском.', 2400.00, 2800.00, 14, (SELECT id FROM categories WHERE slug = 'rumyana' LIMIT 1), 10, 13, 1, 1),
('Румяна MAC Powder Blush', 'mac-powder-blush', 'Пудровые румяна с матовым финишем. Широкая палитра оттенков. Легко наносится, долго держится.', 1800.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'rumyana' LIMIT 1), 4, 15, 0, 1),
('Румяна Dior Rosy Glow', 'dior-rosy-glow', 'Румяна с эффектом здорового румянца. Адаптируются к pH кожи. Естественный розовый оттенок.', 2600.00, 3100.00, 16, (SELECT id FROM categories WHERE slug = 'rumyana' LIMIT 1), 1, 8, 1, 1),
('Румяна Benefit Cosmetics Dandelion', 'benefit-dandelion', 'Культовые румяна с нежным розовым оттенком. Подходят для всех типов кожи. Создают естественный румянец.', 2000.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'rumyana' LIMIT 1), 1, 12, 0, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Макияж - Глаза - Подводки (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Подводка для глаз MAC Liquidlast Liner', 'mac-liquidlast-liner', 'Жидкая подводка для глаз с длительной стойкостью. Не растекается, не стирается до 24 часов. Водостойкая формула.', 1500.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'podvodki' LIMIT 1), 4, 20, 0, 0),
('Подводка для глаз Chanel Stylo Yeux Waterproof', 'chanel-stylo-yeux', 'Водостойкая подводка-карандаш для глаз. Мягкая формула, легко наносится. Стойкость до 12 часов.', 1800.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'podvodki' LIMIT 1), 2, 18, 1, 1),
('Подводка для глаз Dior Diorshow On Stage', 'dior-diorshow-on-stage', 'Жидкая подводка с кисточкой. Идеальная линия, стойкость до 24 часов. Не растекается.', 1900.00, 2200.00, 14, (SELECT id FROM categories WHERE slug = 'podvodki' LIMIT 1), 1, 15, 1, 1),
('Подводка для глаз Urban Decay 24/7 Glide-On Eye Pencil', 'urban-decay-24-7-pencil', 'Кремовая подводка-карандаш. Легко растушевывается, стойкость до 12 часов. Широкая палитра оттенков.', 1600.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'podvodki' LIMIT 1), 11, 16, 0, 1),
('Подводка для глаз Lancome Artliner', 'lancome-artliner', 'Точная жидкая подводка с тонкой кисточкой. Идеальная для создания стрелок. Стойкость до 16 часов.', 1700.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'podvodki' LIMIT 1), 9, 14, 0, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Макияж - Глаза - Туши (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Тушь для ресниц Chanel Le Volume de Chanel', 'chanel-le-volume', 'Объемная тушь для ресниц от Chanel. Создает эффект объемных и длинных ресниц без комкования. Стойкость до 12 часов.', 1800.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'tushi' LIMIT 1), 2, 15, 0, 1),
('Тушь для ресниц Too Faced Better Than Sex', 'too-faced-better-than-sex', 'Легендарная тушь для ресниц. Создает объем и длину, эффект накладных ресниц. Водостойкая формула.', 2000.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'tushi' LIMIT 1), 12, 9, 0, 1),
('Тушь для ресниц Dior Diorshow Pump\'n\'Volume', 'dior-diorshow-pump', 'Тушь с эффектом накачки объема. Уникальная щеточка создает максимальный объем. Стойкость до 16 часов.', 2100.00, 2500.00, 16, (SELECT id FROM categories WHERE slug = 'tushi' LIMIT 1), 1, 12, 1, 1),
('Тушь для ресниц MAC False Lashes Extreme Black', 'mac-false-lashes', 'Тушь для создания эффекта накладных ресниц. Максимальный объем и длина. Водостойкая формула.', 1900.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'tushi' LIMIT 1), 4, 13, 0, 1),
('Тушь для ресниц Lancome Hypnose Drama', 'lancome-hypnose-drama', 'Тушь с драматическим эффектом. Объем и длина в одном продукте. Стойкость до 12 часов.', 1950.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'tushi' LIMIT 1), 9, 11, 1, 1),
('Тушь для ресниц Maybelline Lash Sensational', 'maybelline-lash-sensational', 'Тушь с веерообразной щеточкой. Создает объем и разделяет ресницы. Доступная цена, отличное качество.', 650.00, 850.00, 24, (SELECT id FROM categories WHERE slug = 'tushi' LIMIT 1), 6, 20, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Макияж - Глаза - Тени для век (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Палитра теней Urban Decay Naked', 'urban-decay-naked-palette', 'Культовая палитра теней для век с 12 оттенками. Высокопигментированные тени с кремовой текстурой. Идеальна для создания дневного и вечернего макияжа.', 4500.00, 5200.00, 13, (SELECT id FROM categories WHERE slug = 'teni-dlya-resnits' LIMIT 1), 11, 4, 1, 1),
('Палитра теней MAC Eyeshadow Palette', 'mac-eyeshadow-palette', 'Профессиональная палитра теней. Высокая пигментация, легко растушевывается. Широкая палитра оттенков.', 3800.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'teni-dlya-resnits' LIMIT 1), 4, 6, 0, 1),
('Палитра теней Dior Backstage Eye Palette', 'dior-backstage-palette', 'Палитра теней для профессионального макияжа. 9 оттенков, матовые и перламутровые текстуры. Долгая стойкость.', 4200.00, 4900.00, 14, (SELECT id FROM categories WHERE slug = 'teni-dlya-resnits' LIMIT 1), 1, 5, 1, 1),
('Палитра теней Chanel Les 4 Ombres', 'chanel-les-4-ombres', 'Элегантная палитра из 4 оттенков. Высококачественные тени с шелковистой текстурой. Классические сочетания цветов.', 3500.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'teni-dlya-resnits' LIMIT 1), 2, 7, 0, 0),
('Палитра теней NARS Wanted Eyeshadow Palette', 'nars-wanted-palette', 'Палитра с 12 оттенками. Матовые и мерцающие тени. Идеальна для создания яркого макияжа.', 4100.00, 4800.00, 15, (SELECT id FROM categories WHERE slug = 'teni-dlya-resnits' LIMIT 1), 10, 8, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Макияж - Губы - Губные помады (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Помада Dior Rouge Dior', 'dior-rouge-dior', 'Премиальная помада от Dior с насыщенным цветом и стойкой формулой. Обеспечивает комфортное нанесение и длительное ношение до 8 часов. Широкая палитра оттенков.', 2500.00, 3000.00, 17, (SELECT id FROM categories WHERE slug = 'gubnye-pomady' LIMIT 1), 1, 10, 1, 1),
('Помада MAC Retro Matte Lipstick', 'mac-retro-matte', 'Матовые помады с насыщенным цветом. Стойкость до 8 часов, не сушит губы. Яркие и смелые оттенки.', 2100.00, 2500.00, 16, (SELECT id FROM categories WHERE slug = 'gubnye-pomady' LIMIT 1), 4, 11, 1, 1),
('Помада Chanel Rouge Allure', 'chanel-rouge-allure', 'Элегантная помада с кремовой текстурой. Комфортное нанесение, стойкость до 6 часов. Классические оттенки.', 2800.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'gubnye-pomady' LIMIT 1), 2, 9, 0, 1),
('Помада YSL Rouge Pur Couture', 'ysl-rouge-pur-couture', 'Роскошная помада с шелковистой текстурой. Насыщенные цвета, стойкость до 8 часов. Премиальное качество.', 2700.00, 3200.00, 16, (SELECT id FROM categories WHERE slug = 'gubnye-pomady' LIMIT 1), 3, 8, 1, 1),
('Помада Lancome L\'Absolu Rouge', 'lancome-labsolu-rouge', 'Помада с увлажняющей формулой. Комфортное ношение, стойкость до 6 часов. Широкая палитра оттенков.', 2400.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'gubnye-pomady' LIMIT 1), 9, 12, 0, 1),
('Помада NARS Powermatte Lip Pigment', 'nars-powermatte', 'Жидкая матовая помада. Максимальная стойкость до 12 часов. Не сушит губы, комфортное ношение.', 2200.00, 2600.00, 15, (SELECT id FROM categories WHERE slug = 'gubnye-pomady' LIMIT 1), 10, 10, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Макияж - Губы - Блески для губ (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Блеск для губ Lancome Juicy Tubes', 'lancome-juicy-tubes', 'Блестящий блеск для губ с увлажняющим эффектом. Создает эффект сочных губ. Разнообразные оттенки с фруктовым ароматом.', 1200.00, 1500.00, 20, (SELECT id FROM categories WHERE slug = 'bleski-dlya-gub' LIMIT 1), 9, 18, 0, 1),
('Блеск для губ Dior Addict Lip Maximizer', 'dior-addict-lip-maximizer', 'Блеск с эффектом увеличения губ. Охлаждающий эффект, увлажняющая формула. Придает объем губам.', 1900.00, 2300.00, 17, (SELECT id FROM categories WHERE slug = 'bleski-dlya-gub' LIMIT 1), 1, 15, 1, 1),
('Блеск для губ MAC Lipglass', 'mac-lipglass', 'Глянцевый блеск для губ. Высокий блеск, стойкость до 4 часов. Широкая палитра оттенков.', 1400.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'bleski-dlya-gub' LIMIT 1), 4, 16, 0, 1),
('Блеск для губ Chanel Rouge Coco Flash', 'chanel-rouge-coco-flash', 'Блеск-помада с высокой степенью блеска. Увлажняющая формула, комфортное ношение. Элегантные оттенки.', 2100.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'bleski-dlya-gub' LIMIT 1), 2, 12, 1, 1),
('Блеск для губ YSL Volupte Tint-in-Balm', 'ysl-volupte-tint', 'Блеск с легким оттенком и увлажняющим эффектом. Комфортное ношение, естественный цвет. Ежедневный уход за губами.', 1800.00, 2200.00, 18, (SELECT id FROM categories WHERE slug = 'bleski-dlya-gub' LIMIT 1), 3, 14, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Макияж - Губы - Гигиенические помады (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Гигиеническая помада Dior Lip Glow', 'dior-lip-glow', 'Гигиеническая помада с эффектом здорового румянца. Адаптируется к pH губ. Увлажняет и защищает.', 2200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'gigienicheskie-pomady' LIMIT 1), 1, 13, 1, 1),
('Гигиеническая помада Chanel Rouge Coco Baume', 'chanel-rouge-coco-baume', 'Увлажняющий бальзам для губ с легким оттенком. Защищает от сухости, придает естественный цвет.', 2000.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'gigienicheskie-pomady' LIMIT 1), 2, 15, 0, 1),
('Гигиеническая помада Lancome Juicy Shaker', 'lancome-juicy-shaker', 'Увлажняющий бальзам для губ с фруктовым ароматом. Легкий оттенок, комфортное ношение.', 1500.00, 1800.00, 17, (SELECT id FROM categories WHERE slug = 'gigienicheskie-pomady' LIMIT 1), 9, 17, 1, 1),
('Гигиеническая помада MAC Lip Conditioner', 'mac-lip-conditioner', 'Увлажняющий бальзам для губ. Защищает от сухости и трещин. Без отдушек, подходит для чувствительных губ.', 1200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'gigienicheskie-pomady' LIMIT 1), 4, 19, 0, 0),
('Гигиеническая помада NARS Afterglow Lip Balm', 'nars-afterglow-balm', 'Увлажняющий бальзам с легким оттенком. SPF 30, защищает от солнца. Комфортное ношение.', 1800.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'gigienicheskie-pomady' LIMIT 1), 10, 14, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Парфюмерия - Женские ароматы (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Духи Chanel No.5 Eau de Parfum', 'chanel-no5-edp', 'Культовый женский аромат от Chanel. Классический букет с нотами иланг-иланга, нероли и альдегидов. Символ элегантности и роскоши.', 8500.00, 10000.00, 15, (SELECT id FROM categories WHERE slug = 'zhenskie-aromaty' LIMIT 1), 2, 5, 1, 1),
('Духи Dior J\'adore Eau de Parfum', 'dior-jadore-edp', 'Элегантный женский аромат с нотами иланг-иланга, розы и жасмина. Символ женственности и роскоши. Цветочный букет.', 7200.00, 8500.00, 15, (SELECT id FROM categories WHERE slug = 'zhenskie-aromaty' LIMIT 1), 1, 6, 1, 1),
('Духи Lancome La Vie Est Belle', 'lancome-la-vie-est-belle', 'Сладкий женский аромат с нотами ириса, пачули и ванили. Оптимистичный и жизнерадостный. Долгая стойкость.', 6800.00, 8000.00, 15, (SELECT id FROM categories WHERE slug = 'zhenskie-aromaty' LIMIT 1), 9, 7, 1, 1),
('Духи Yves Saint Laurent Black Opium', 'ysl-black-opium', 'Загадочный женский аромат с нотами кофе, ванили и белых цветов. Смелый и чувственный. Идеален для вечера.', 7500.00, 9000.00, 17, (SELECT id FROM categories WHERE slug = 'zhenskie-aromaty' LIMIT 1), 3, 5, 1, 1),
('Духи Estee Lauder Beautiful', 'estee-lauder-beautiful', 'Классический женский аромат с нотами розы, жасмина и лилии. Элегантный и утонченный. Подходит для любого случая.', 6500.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'zhenskie-aromaty' LIMIT 1), 7, 8, 0, 1),
('Духи Clinique Happy', 'clinique-happy', 'Свежий и легкий женский аромат с нотами цитрусовых и цветов. Оптимистичный и энергичный. Идеален для дневного ношения.', 5500.00, 6500.00, 15, (SELECT id FROM categories WHERE slug = 'zhenskie-aromaty' LIMIT 1), 8, 10, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Парфюмерия - Мужские ароматы (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Туалетная вода Yves Saint Laurent Y Eau de Toilette', 'ysl-y-edt', 'Современный мужской аромат с нотами яблока, имбиря и сандала. Свежий и энергичный. Идеален для активных мужчин.', 5500.00, 6500.00, 15, (SELECT id FROM categories WHERE slug = 'muzhskie-aromaty' LIMIT 1), 3, 8, 1, 1),
('Туалетная вода Dior Sauvage Eau de Toilette', 'dior-sauvage-edt', 'Легендарный мужской аромат с нотами перца, бергамота и амброксана. Смелый и дерзкий. Символ современной мужественности.', 6200.00, 7500.00, 17, (SELECT id FROM categories WHERE slug = 'muzhskie-aromaty' LIMIT 1), 1, 7, 1, 1),
('Туалетная вода Chanel Bleu de Chanel', 'chanel-bleu-de-chanel', 'Универсальный мужской аромат с нотами цитрусовых, мяты и сандала. Свежий и элегантный. Подходит для любого случая.', 6800.00, 8000.00, 15, (SELECT id FROM categories WHERE slug = 'muzhskie-aromaty' LIMIT 1), 2, 6, 1, 1),
('Туалетная вода Lancome Hypnose Homme', 'lancome-hypnose-homme', 'Чувственный мужской аромат с нотами лаванды, ванили и ветивера. Загадочный и притягательный.', 6000.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'muzhskie-aromaty' LIMIT 1), 9, 9, 0, 1),
('Туалетная вода Clinique Happy for Men', 'clinique-happy-men', 'Свежий мужской аромат с нотами цитрусовых и специй. Оптимистичный и энергичный. Идеален для дневного ношения.', 4800.00, 5800.00, 17, (SELECT id FROM categories WHERE slug = 'muzhskie-aromaty' LIMIT 1), 8, 10, 0, 1),
('Туалетная вода Estee Lauder Pleasures for Men', 'estee-lauder-pleasures-men', 'Свежий мужской аромат с нотами лаванды, бергамота и мускуса. Легкий и приятный. Подходит для ежедневного использования.', 5200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'muzhskie-aromaty' LIMIT 1), 7, 8, 0, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Парфюмерия - Унисекс ароматы (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Туалетная вода Calvin Klein CK One', 'ck-one-edt', 'Культовый унисекс аромат от Calvin Klein. Свежий и легкий, подходит для всех. Ноты цитрусовых, зеленого чая и мускуса.', 4800.00, 5800.00, 17, (SELECT id FROM categories WHERE slug = 'uniseks-aromaty' LIMIT 1), 1, 10, 1, 1),
('Туалетная вода Jo Malone Lime Basil & Mandarin', 'jo-malone-lime-basil', 'Унисекс аромат с нотами лайма, базилика и мандарина. Свежий и цитрусовый. Идеален для лета.', 6500.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'uniseks-aromaty' LIMIT 1), 1, 6, 0, 1),
('Туалетная вода Maison Margiela Replica', 'maison-margiela-replica', 'Унисекс аромат, воссоздающий атмосферу определенного места и времени. Уникальные композиции. Минималистичный дизайн.', 7200.00, 8500.00, 15, (SELECT id FROM categories WHERE slug = 'uniseks-aromaty' LIMIT 1), 1, 5, 1, 1),
('Туалетная вода Byredo Gypsy Water', 'byredo-gypsy-water', 'Унисекс аромат с нотами можжевельника, сосны и ванили. Загадочный и свободный дух. Премиальное качество.', 8500.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'uniseks-aromaty' LIMIT 1), 1, 4, 1, 1),
('Туалетная вода Le Labo Santal 33', 'le-labo-santal-33', 'Культовый унисекс аромат с нотами сандала, фиалки и кардамона. Уникальный и запоминающийся. Премиальная нишевая парфюмерия.', 12000.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'uniseks-aromaty' LIMIT 1), 1, 3, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Волосы - Сухие шампуни (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Сухой шампунь L\'Oreal Elvive Dream Lengths', 'loreal-elvive-dry-shampoo', 'Сухой шампунь для быстрого обновления волос. Впитывает излишки жира, придает объем. Не оставляет белого налета.', 450.00, 600.00, 25, (SELECT id FROM categories WHERE slug = 'suhie-shampuni' LIMIT 1), 5, 25, 0, 1),
('Сухой шампунь Batiste Original', 'batiste-original', 'Классический сухой шампунь. Быстро впитывает жир, освежает волосы. Доступная цена, отличное качество.', 350.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'suhie-shampuni' LIMIT 1), 5, 30, 0, 0),
('Сухой шампунь Klorane Oat Milk', 'klorane-oat-milk', 'Сухой шампунь с овсяным молоком. Мягкая формула, подходит для чувствительной кожи головы. Не сушит волосы.', 550.00, 700.00, 21, (SELECT id FROM categories WHERE slug = 'suhie-shampuni' LIMIT 1), 5, 20, 1, 1),
('Сухой шампунь Living Proof Perfect Hair Day', 'living-proof-dry-shampoo', 'Премиальный сухой шампунь. Не оставляет следов, придает объем и текстуру. Долгая свежесть волос.', 1200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'suhie-shampuni' LIMIT 1), 5, 15, 1, 1),
('Сухой шампунь Moroccanoil Dry Shampoo', 'moroccanoil-dry-shampoo', 'Сухой шампунь с аргановым маслом. Увлажняет и освежает волосы. Не утяжеляет, придает блеск.', 900.00, 1100.00, 18, (SELECT id FROM categories WHERE slug = 'suhie-shampuni' LIMIT 1), 5, 18, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Волосы - Кондиционеры и бальзамы (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Маска для волос Maybelline Total Repair', 'maybelline-total-repair-mask', 'Интенсивная маска для восстановления волос. Глубокое питание и увлажнение. Восстанавливает поврежденные волосы.', 550.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'konditsionery-i-balzamy' LIMIT 1), 6, 22, 0, 0),
('Кондиционер L\'Oreal Elvive Total Repair 5', 'loreal-elvive-conditioner', 'Восстанавливающий кондиционер для поврежденных волос. Обогащен протеинами и кератином. Восстанавливает структуру волос.', 400.00, 550.00, 27, (SELECT id FROM categories WHERE slug = 'konditsionery-i-balzamy' LIMIT 1), 5, 20, 0, 1),
('Маска для волос Moroccanoil Intense Hydrating Mask', 'moroccanoil-hydrating-mask', 'Интенсивная увлажняющая маска с аргановым маслом. Глубокое питание, придает блеск и мягкость.', 1800.00, 2200.00, 18, (SELECT id FROM categories WHERE slug = 'konditsionery-i-balzamy' LIMIT 1), 5, 12, 1, 1),
('Кондиционер Redken All Soft Heavy Cream', 'redken-all-soft', 'Богатый кондиционер для сухих и поврежденных волос. Мгновенно смягчает и увлажняет. Профессиональное качество.', 1400.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'konditsionery-i-balzamy' LIMIT 1), 5, 16, 0, 1),
('Маска для волос Olaplex No.3 Hair Perfector', 'olaplex-no3', 'Восстанавливающая маска для поврежденных волос. Восстанавливает разорванные связи в структуре волос. Профессиональное средство.', 2500.00, 3000.00, 17, (SELECT id FROM categories WHERE slug = 'konditsionery-i-balzamy' LIMIT 1), 5, 8, 1, 1),
('Кондиционер Pantene Pro-V Repair & Protect', 'pantene-repair-protect', 'Кондиционер для поврежденных волос. Защищает от дальнейшего повреждения, восстанавливает структуру. Доступная цена.', 350.00, 450.00, 22, (SELECT id FROM categories WHERE slug = 'konditsionery-i-balzamy' LIMIT 1), 5, 25, 0, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Волосы - Стайлинг - Термозащита (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Термозащита L\'Oreal Professionnel Tecni Art', 'loreal-tecni-art', 'Спрей для защиты волос от термического воздействия. Защищает до 230°C. Не утяжеляет волосы.', 650.00, 800.00, 19, (SELECT id FROM categories WHERE slug = 'termozashchita' LIMIT 1), 5, 15, 0, 1),
('Термозащита Redken Iron Shape 11', 'redken-iron-shape', 'Термозащита с эффектом выпрямления. Защищает до 232°C, облегчает укладку. Профессиональное средство.', 1200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'termozashchita' LIMIT 1), 5, 12, 1, 1),
('Термозащита Moroccanoil Perfect Defense', 'moroccanoil-perfect-defense', 'Термозащита с аргановым маслом. Защищает до 232°C, придает блеск. Не утяжеляет волосы.', 1100.00, 1300.00, 15, (SELECT id FROM categories WHERE slug = 'termozashchita' LIMIT 1), 5, 13, 0, 1),
('Термозащита Living Proof Restore Perfecting Spray', 'living-proof-restore', 'Термозащита с восстанавливающим эффектом. Защищает от тепла до 232°C, восстанавливает структуру.', 1500.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'termozashchita' LIMIT 1), 5, 10, 1, 1),
('Термозащита Kérastase Nutritive Nectar Thermique', 'kerastase-nectar-thermique', 'Термозащита для сухих волос. Защищает до 230°C, питает и увлажняет. Премиальное качество.', 1800.00, 2200.00, 18, (SELECT id FROM categories WHERE slug = 'termozashchita' LIMIT 1), 5, 8, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Волосы - Стайлинг - Фиксация (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Лак для волос L\'Oreal Elnett Satin', 'loreal-elnett', 'Лак для волос сильной фиксации. Не утяжеляет волосы, легко расчесывается. Классическая формула.', 350.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'fiksatsiya' LIMIT 1), 5, 30, 0, 0),
('Лак для волос Redken Forceful 23', 'redken-forceful', 'Лак для волос максимальной фиксации. Удерживает прическу до 24 часов. Профессиональное качество.', 800.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'fiksatsiya' LIMIT 1), 5, 20, 0, 1),
('Лак для волос Moroccanoil Luminous Hairspray', 'moroccanoil-luminous', 'Лак для волос с эффектом блеска. Средняя фиксация, не утяжеляет. Придает волосам сияние.', 950.00, 1150.00, 17, (SELECT id FROM categories WHERE slug = 'fiksatsiya' LIMIT 1), 5, 18, 1, 1),
('Лак для волос Living Proof Flex Shaping Hairspray', 'living-proof-flex', 'Лак для волос с гибкой фиксацией. Позволяет менять прическу в течение дня. Не оставляет липкости.', 1200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'fiksatsiya' LIMIT 1), 5, 15, 1, 1),
('Лак для волос Kérastase Laque Couture', 'kerastase-laque-couture', 'Премиальный лак для волос. Сильная фиксация, не утяжеляет. Элегантный аромат.', 1400.00, 1700.00, 18, (SELECT id FROM categories WHERE slug = 'fiksatsiya' LIMIT 1), 5, 12, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Волосы - Стайлинг - Придание объема (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Спрей для объема L\'Oreal Elvive Volume Extend', 'loreal-volume-extend', 'Спрей для придания объема волосам. Легкая формула, не утяжеляет. Стойкий эффект объема.', 420.00, 550.00, 24, (SELECT id FROM categories WHERE slug = 'pridanie-obema' LIMIT 1), 5, 18, 0, 1),
('Спрей для объема Redken Guts 10', 'redken-guts', 'Спрей для максимального объема. Создает текстуру и объем у корней. Профессиональное средство.', 900.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'pridanie-obema' LIMIT 1), 5, 16, 1, 1),
('Спрей для объема Moroccanoil Volumizing Mousse', 'moroccanoil-volumizing', 'Мусс для придания объема. Легкая формула, не утяжеляет. Придает объем и текстуру.', 1100.00, 1300.00, 15, (SELECT id FROM categories WHERE slug = 'pridanie-obema' LIMIT 1), 5, 14, 0, 1),
('Спрей для объема Living Proof Full Thickening Cream', 'living-proof-full', 'Крем для придания объема и густоты. Увеличивает диаметр каждого волоса. Не утяжеляет.', 1500.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'pridanie-obema' LIMIT 1), 5, 12, 1, 1),
('Спрей для объема Kérastase Densifique Mousse', 'kerastase-densifique', 'Мусс для придания объема и густоты. Укрепляет волосы, создает объем. Премиальное качество.', 1600.00, 1900.00, 16, (SELECT id FROM categories WHERE slug = 'pridanie-obema' LIMIT 1), 5, 10, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Маникюр - Средства для снятия лака (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Жидкость для снятия лака OPI Expert Touch', 'opi-expert-touch', 'Эффективная жидкость для снятия лака. Бережно удаляет лак, не сушит ногти. Содержит увлажняющие компоненты.', 350.00, 450.00, 22, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-snyatiya-laka' LIMIT 1), 1, 20, 0, 1),
('Жидкость для снятия гель-лака CND Remove', 'cnd-remove', 'Специальная жидкость для снятия гель-лака. Быстро и эффективно удаляет стойкое покрытие. Не повреждает ногтевую пластину.', 450.00, 600.00, 25, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-snyatiya-laka' LIMIT 1), 1, 15, 0, 1),
('Жидкость для снятия лака Essie Quick-E', 'essie-quick-e', 'Быстрая жидкость для снятия лака. Эффективно удаляет лак за секунды. Не сушит ногти.', 400.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-snyatiya-laka' LIMIT 1), 1, 18, 0, 0),
('Жидкость для снятия лака Zoya Remove+', 'zoya-remove', 'Эффективная жидкость для снятия лака. Бережная формула, не сушит ногти. Подходит для чувствительных ногтей.', 500.00, 650.00, 23, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-snyatiya-laka' LIMIT 1), 1, 16, 1, 1),
('Жидкость для снятия лака Butter London Melt Away', 'butter-london-melt', 'Премиальная жидкость для снятия лака. Быстро удаляет лак, увлажняет ногти. Не оставляет жирной пленки.', 600.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-snyatiya-laka' LIMIT 1), 1, 14, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Маникюр - Гель лаки (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Гель-лак OPI Infinite Shine', 'opi-infinite-shine', 'Гель-лак с длительной стойкостью до 11 дней. Блестящее покрытие, легко наносится. Широкая палитра оттенков.', 1200.00, 1500.00, 20, (SELECT id FROM categories WHERE slug = 'gel-laki' LIMIT 1), 1, 12, 1, 1),
('Гель-лак CND Shellac', 'cnd-shellac', 'Профессиональный гель-лак с идеальной стойкостью. Стойкость до 14 дней. Широкая палитра оттенков.', 1300.00, 1600.00, 19, (SELECT id FROM categories WHERE slug = 'gel-laki' LIMIT 1), 1, 10, 1, 1),
('Гель-лак Essie Gel Couture', 'essie-gel-couture', 'Гель-лак с эффектом салонного маникюра. Стойкость до 10 дней. Легкое нанесение и снятие.', 1100.00, 1350.00, 19, (SELECT id FROM categories WHERE slug = 'gel-laki' LIMIT 1), 1, 13, 1, 1),
('Гель-лак Dior Vernis Gel Shine & Wear', 'dior-gel-vernis', 'Премиальный гель-лак от Dior. Стойкость до 7 дней, блестящее покрытие. Элегантные оттенки.', 1800.00, 2200.00, 18, (SELECT id FROM categories WHERE slug = 'gel-laki' LIMIT 1), 1, 8, 1, 1),
('Гель-лак Chanel Le Vernis Longwear', 'chanel-le-vernis-longwear', 'Гель-лак с длительной стойкостью. Стойкость до 7 дней, премиальное качество. Классические оттенки.', 2000.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'gel-laki' LIMIT 1), 2, 7, 1, 1),
('Гель-лак Zoya Gelie Cure', 'zoya-gelie-cure', 'Гель-лак без УФ-лампы. Стойкость до 14 дней, легко наносится. Безопасная формула.', 1400.00, 1700.00, 18, (SELECT id FROM categories WHERE slug = 'gel-laki' LIMIT 1), 1, 9, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Маникюр - Лаки для ногтей (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Лак для ногтей Essie Nail Polish', 'essie-nail-polish', 'Лак для ногтей с широкой палитрой оттенков. Стойкое покрытие, быстро сохнет. Кремовая и перламутровая текстуры.', 450.00, 600.00, 25, (SELECT id FROM categories WHERE slug = 'laki-dlya-nogtey' LIMIT 1), 1, 25, 0, 1),
('Лак для ногтей OPI Nail Lacquer', 'opi-nail-lacquer', 'Профессиональный лак для ногтей. Долговечное покрытие, насыщенные цвета. Широкая палитра оттенков.', 550.00, 700.00, 21, (SELECT id FROM categories WHERE slug = 'laki-dlya-nogtey' LIMIT 1), 1, 20, 0, 1),
('Лак для ногтей Chanel Le Vernis', 'chanel-le-vernis', 'Премиальный лак для ногтей от Chanel. Элегантные оттенки, стойкое покрытие. Классические цвета.', 1200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'laki-dlya-nogtey' LIMIT 1), 2, 8, 1, 1),
('Лак для ногтей Dior Vernis', 'dior-vernis', 'Роскошный лак для ногтей от Dior. Стойкое покрытие, элегантные оттенки. Премиальное качество.', 1300.00, 1600.00, 19, (SELECT id FROM categories WHERE slug = 'laki-dlya-nogtey' LIMIT 1), 1, 7, 1, 1),
('Лак для ногтей Zoya Nail Polish', 'zoya-nail-polish', 'Экологичный лак для ногтей. Без вредных компонентов, стойкое покрытие. Широкая палитра оттенков.', 600.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'laki-dlya-nogtey' LIMIT 1), 1, 18, 0, 1),
('Лак для ногтей Butter London Nail Lacquer', 'butter-london-lacquer', 'Премиальный лак для ногтей. Стойкое покрытие, уникальные оттенки. Без формальдегида и толуола.', 800.00, 1000.00, 20, (SELECT id FROM categories WHERE slug = 'laki-dlya-nogtey' LIMIT 1), 1, 15, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Мужчины - Волосы - Шампунь (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Шампунь для мужчин L\'Oreal Men Expert Barber Club', 'loreal-men-expert-shampoo', 'Шампунь для мужчин с укрепляющей формулой. Подходит для ежедневного использования. Освежающий аромат.', 350.00, 450.00, 22, (SELECT id FROM categories WHERE slug = 'shampun-muzhchiny' LIMIT 1), 5, 20, 0, 1),
('Шампунь для мужчин Head & Shoulders Men', 'head-shoulders-men', 'Шампунь против перхоти для мужчин. Эффективно борется с перхотью, успокаивает кожу головы. Подходит для ежедневного использования.', 280.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'shampun-muzhchiny' LIMIT 1), 5, 25, 0, 0),
('Шампунь для мужчин Nivea Men Silver Protect', 'nivea-men-silver', 'Шампунь для мужчин с защитой от седины. Укрепляет волосы, придает блеск. Подходит для всех типов волос.', 320.00, 400.00, 20, (SELECT id FROM categories WHERE slug = 'shampun-muzhchiny' LIMIT 1), 5, 22, 0, 1),
('Шампунь для мужчин Redken Brews Daily Shampoo', 'redken-brews', 'Профессиональный шампунь для мужчин. Очищает и укрепляет волосы. Подходит для всех типов волос.', 650.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'shampun-muzhchiny' LIMIT 1), 5, 18, 1, 1),
('Шампунь для мужчин American Crew Daily Shampoo', 'american-crew-daily', 'Шампунь для ежедневного использования. Мягкая формула, подходит для всех типов волос. Профессиональное качество.', 550.00, 700.00, 21, (SELECT id FROM categories WHERE slug = 'shampun-muzhchiny' LIMIT 1), 5, 16, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Мужчины - Волосы - Кондиционер (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Кондиционер для мужчин L\'Oreal Men Expert', 'loreal-men-expert-conditioner', 'Кондиционер для мужчин с укрепляющей формулой. Смягчает и увлажняет волосы. Подходит для ежедневного использования.', 320.00, 400.00, 20, (SELECT id FROM categories WHERE slug = 'konditsioner-muzhchiny' LIMIT 1), 5, 20, 0, 1),
('Кондиционер для мужчин Head & Shoulders Men', 'head-shoulders-men-conditioner', 'Кондиционер против перхоти для мужчин. Успокаивает кожу головы, смягчает волосы. Подходит для ежедневного использования.', 280.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'konditsioner-muzhchiny' LIMIT 1), 5, 22, 0, 0),
('Кондиционер для мужчин Nivea Men', 'nivea-men-conditioner', 'Кондиционер для мужчин с увлажняющей формулой. Смягчает волосы, облегчает расчесывание. Подходит для всех типов волос.', 300.00, 380.00, 21, (SELECT id FROM categories WHERE slug = 'konditsioner-muzhchiny' LIMIT 1), 5, 21, 0, 1),
('Кондиционер для мужчин Redken Brews Daily Conditioner', 'redken-brews-conditioner', 'Профессиональный кондиционер для мужчин. Увлажняет и укрепляет волосы. Подходит для всех типов волос.', 600.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'konditsioner-muzhchiny' LIMIT 1), 5, 17, 1, 1),
('Кондиционер для мужчин American Crew Daily Conditioner', 'american-crew-conditioner', 'Кондиционер для ежедневного использования. Мягкая формула, не утяжеляет волосы. Профессиональное качество.', 520.00, 650.00, 20, (SELECT id FROM categories WHERE slug = 'konditsioner-muzhchiny' LIMIT 1), 5, 18, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Мужчины - Бритье - Триммеры и станки (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Электрический триммер Philips Series 7000', 'philips-trimmer-7000', 'Многофункциональный триммер для бороды и усов. Водонепроницаемый, самозатачивающиеся лезвия. Подходит для сухого и влажного бритья.', 3500.00, 4200.00, 17, (SELECT id FROM categories WHERE slug = 'trimmery-i-stanki' LIMIT 1), 1, 5, 1, 1),
('Бритвенный станок Gillette Fusion5 ProGlide', 'gillette-fusion5', 'Бритвенный станок с 5 лезвиями. Плавное скольжение, точное бритье. Подходит для чувствительной кожи.', 450.00, 600.00, 25, (SELECT id FROM categories WHERE slug = 'trimmery-i-stanki' LIMIT 1), 1, 12, 0, 1),
('Электрическая бритва Braun Series 9', 'braun-series-9', 'Электрическая бритва премиум-класса. 4 бреющих элемента, адаптивная система. Подходит для сухого и влажного бритья.', 12000.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'trimmery-i-stanki' LIMIT 1), 1, 3, 1, 1),
('Триммер для бороды Wahl Professional', 'wahl-professional-trimmer', 'Профессиональный триммер для бороды. Точная настройка длины, мощный мотор. Идеален для создания стильных бород.', 2800.00, 3400.00, 18, (SELECT id FROM categories WHERE slug = 'trimmery-i-stanki' LIMIT 1), 1, 6, 1, 1),
('Бритвенный станок Wilkinson Sword Classic', 'wilkinson-sword-classic', 'Классический бритвенный станок с 3 лезвиями. Точное бритье, подходит для ежедневного использования. Доступная цена.', 250.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'trimmery-i-stanki' LIMIT 1), 1, 20, 0, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Мужчины - Бритье - Средства для бритья (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Пена для бритья Gillette Fusion ProGlide', 'gillette-fusion-foam', 'Пена для бритья с увлажняющими компонентами. Обеспечивает гладкое и комфортное бритье. Подходит для чувствительной кожи.', 250.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-britya' LIMIT 1), 1, 30, 0, 0),
('Крем для бритья Proraso', 'proraso-shaving-cream', 'Итальянский крем для бритья премиум-класса. Богатая пена, успокаивает кожу. Классическая формула.', 450.00, 600.00, 25, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-britya' LIMIT 1), 1, 18, 1, 1),
('Гель для бритья Nivea Men Sensitive', 'nivea-men-sensitive-gel', 'Гель для бритья для чувствительной кожи. Успокаивает и защищает кожу. Подходит для ежедневного использования.', 280.00, 350.00, 20, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-britya' LIMIT 1), 1, 22, 0, 1),
('Крем для бритья The Art of Shaving', 'art-of-shaving-cream', 'Премиальный крем для бритья. Богатая пена, увлажняет кожу. Профессиональное качество.', 1200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-britya' LIMIT 1), 1, 12, 1, 1),
('Пена для бритья L\'Oreal Men Expert Barber Club', 'loreal-men-expert-foam', 'Пена для бритья с увлажняющими компонентами. Обеспечивает комфортное бритье. Освежающий аромат.', 300.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-britya' LIMIT 1), 5, 25, 0, 0),
('Масло для бритья American Crew', 'american-crew-oil', 'Масло для бритья премиум-класса. Обеспечивает гладкое скольжение бритвы. Увлажняет и защищает кожу.', 800.00, 1000.00, 20, (SELECT id FROM categories WHERE slug = 'sredstva-dlya-britya' LIMIT 1), 1, 15, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Мужчины - Бритье - Средства после бритья (5 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Лосьон после бритья Gillette After Shave', 'gillette-after-shave', 'Лосьон после бритья с успокаивающим эффектом. Снимает раздражение, освежает кожу. Подходит для ежедневного использования.', 350.00, 450.00, 22, (SELECT id FROM categories WHERE slug = 'sredstva-posle-britya' LIMIT 1), 1, 20, 0, 1),
('Бальзам после бритья Nivea Men', 'nivea-men-after-shave', 'Бальзам после бритья с увлажняющим эффектом. Успокаивает кожу, снимает раздражение. Подходит для чувствительной кожи.', 320.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'sredstva-posle-britya' LIMIT 1), 1, 22, 0, 0),
('Лосьон после бритья Proraso', 'proraso-after-shave', 'Итальянский лосьон после бритья. Освежает и успокаивает кожу. Классический аромат.', 500.00, 650.00, 23, (SELECT id FROM categories WHERE slug = 'sredstva-posle-britya' LIMIT 1), 1, 18, 1, 1),
('Бальзам после бритья The Art of Shaving', 'art-of-shaving-balm', 'Премиальный бальзам после бритья. Глубоко увлажняет и успокаивает кожу. Профессиональное качество.', 1100.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'sredstva-posle-britya' LIMIT 1), 1, 14, 1, 1),
('Лосьон после бритья L\'Oreal Men Expert', 'loreal-men-expert-after-shave', 'Лосьон после бритья с укрепляющим эффектом. Успокаивает кожу, освежает. Подходит для ежедневного использования.', 380.00, 480.00, 21, (SELECT id FROM categories WHERE slug = 'sredstva-posle-britya' LIMIT 1), 5, 19, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Мужчины - Уход - Для душа и ванны (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Гель для душа мужской Nivea Men', 'nivea-men-shower-gel', 'Очищающий гель для душа для мужчин. Освежающий аромат, подходит для ежедневного использования. Увлажняет кожу.', 280.00, 350.00, 20, (SELECT id FROM categories WHERE slug = 'dlya-dusha-i-vanny' LIMIT 1), 1, 25, 0, 1),
('Гель для душа L\'Oreal Men Expert', 'loreal-men-expert-shower', 'Гель для душа для мужчин с укрепляющей формулой. Освежает и тонизирует кожу. Подходит для ежедневного использования.', 320.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dlya-dusha-i-vanny' LIMIT 1), 5, 23, 0, 0),
('Гель для душа Old Spice', 'old-spice-shower', 'Классический гель для душа для мужчин. Яркий аромат, очищает и освежает. Подходит для ежедневного использования.', 250.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dlya-dusha-i-vanny' LIMIT 1), 1, 28, 0, 0),
('Гель для душа Axe', 'axe-shower-gel', 'Гель для душа для мужчин с ярким ароматом. Очищает и освежает кожу. Широкая линейка ароматов.', 220.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dlya-dusha-i-vanny' LIMIT 1), 1, 30, 0, 0),
('Гель для душа American Crew', 'american-crew-shower', 'Премиальный гель для душа для мужчин. Очищает и увлажняет кожу. Профессиональное качество.', 550.00, 700.00, 21, (SELECT id FROM categories WHERE slug = 'dlya-dusha-i-vanny' LIMIT 1), 1, 20, 1, 1),
('Гель для душа Molton Brown', 'molton-brown-shower', 'Роскошный гель для душа премиум-класса. Увлажняет и питает кожу. Элегантные ароматы.', 1200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dlya-dusha-i-vanny' LIMIT 1), 1, 15, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Мужчины - Уход - Дезодоранты (6 товаров)
INSERT INTO products (name, slug, description, price, old_price, discount, category_id, brand_id, stock, is_new, is_featured) VALUES
('Дезодорант-антиперспирант Rexona Men', 'rexona-men-deodorant', 'Дезодорант-антиперспирант для мужчин. Защита от пота и запаха до 48 часов. Надежная защита.', 180.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dezodoranty' LIMIT 1), 1, 35, 0, 0),
('Дезодорант-антиперспирант Nivea Men', 'nivea-men-deodorant', 'Дезодорант для мужчин с увлажняющим эффектом. Защита до 48 часов. Не раздражает кожу.', 200.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dezodoranty' LIMIT 1), 1, 32, 0, 0),
('Дезодорант-антиперспирант Old Spice', 'old-spice-deodorant', 'Классический дезодорант для мужчин. Яркий аромат, защита до 48 часов. Надежная защита от пота.', 190.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dezodoranty' LIMIT 1), 1, 33, 0, 0),
('Дезодорант-антиперспирант Axe', 'axe-deodorant', 'Дезодорант для мужчин с ярким ароматом. Защита до 48 часов. Широкая линейка ароматов.', 170.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dezodoranty' LIMIT 1), 1, 36, 0, 0),
('Дезодорант-антиперспирант L\'Oreal Men Expert', 'loreal-men-expert-deodorant', 'Дезодорант для мужчин с укрепляющим эффектом. Защита до 48 часов. Освежающий аромат.', 250.00, 320.00, 22, (SELECT id FROM categories WHERE slug = 'dezodoranty' LIMIT 1), 5, 28, 0, 1),
('Дезодорант-антиперспирант American Crew', 'american-crew-deodorant', 'Премиальный дезодорант для мужчин. Защита до 48 часов, элегантный аромат. Профессиональное качество.', 450.00, NULL, 0, (SELECT id FROM categories WHERE slug = 'dezodoranty' LIMIT 1), 1, 24, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- ========== ПРОМОКОДЫ ==========
INSERT INTO promo_codes (code, description, discount_type, discount_value, min_order_amount, max_discount, max_uses, is_active, valid_from, valid_until) VALUES
('FIRST50', 'Скидка 50% на первый заказ', 'percentage', 50.00, 0.00, 2000.00, 100, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('WELCOME10', 'Скидка 10% для новых клиентов', 'percentage', 10.00, 1000.00, NULL, 500, 1, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH)),
('SUMMER20', 'Летняя скидка 20%', 'percentage', 20.00, 2000.00, 3000.00, 200, 1, NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH)),
('FIXED500', 'Скидка 500 рублей', 'fixed', 500.00, 3000.00, NULL, 150, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)),
('NEWYEAR30', 'Новогодняя скидка 30%', 'percentage', 30.00, 1500.00, 5000.00, 300, 1, NOW(), DATE_ADD(NOW(), INTERVAL 2 MONTH)),
('VIP15', 'Скидка 15% для постоянных клиентов', 'percentage', 15.00, 2500.00, NULL, NULL, 1, NOW(), NULL),
('BIRTHDAY25', 'Скидка 25% в день рождения', 'percentage', 25.00, 1000.00, 2000.00, NULL, 1, NOW(), NULL),
('FREESHIP', 'Бесплатная доставка', 'fixed', 0.00, 5000.00, NULL, 1000, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))
ON DUPLICATE KEY UPDATE code=VALUES(code);

-- ========== МАГАЗИНЫ ==========
INSERT INTO shops (name, address, latitude, longitude, description, how_to_get, phone, work_hours) VALUES
('Косметика на Красной площади', 'ул. Никольская, д. 15, Москва', 55.7558, 37.6173, 'Флагманский магазин косметики в центре Москвы. Широкий ассортимент премиальных брендов.', 'Метро: Охотный ряд, Площадь Революции. Выход к Красной площади.', '+7 (495) 123-45-67', 'Ежедневно с 10:00 до 22:00'),
('Косметика на Тверской', 'ул. Тверская, д. 8, Москва', 55.7558, 37.6100, 'Магазин косметики в центре города. Удобное расположение, профессиональные консультанты.', 'Метро: Тверская, Пушкинская. 5 минут пешком от метро.', '+7 (495) 234-56-78', 'Пн-Сб: 10:00-21:00, Вс: 11:00-20:00'),
('Косметика в ТЦ Авиапарк', 'Ходынский бульвар, д. 4, ТЦ Авиапарк, Москва', 55.7890, 37.5300, 'Магазин в крупном торговом центре. Большой выбор товаров, акции и скидки.', 'Метро: Динамо, Сокол. Автобусы 105, 110 до остановки Авиапарк.', '+7 (495) 345-67-89', 'Ежедневно с 10:00 до 22:00'),
('Косметика на Арбате', 'ул. Арбат, д. 45, Москва', 55.7520, 37.5920, 'Уютный магазин на знаменитой улице Арбат. Индивидуальный подход к каждому клиенту.', 'Метро: Арбатская, Смоленская. Пешком по Арбату.', '+7 (495) 456-78-90', 'Пн-Пт: 11:00-20:00, Сб-Вс: 10:00-21:00'),
('Косметика в МЕГА', 'Химки, МКАД 66-й км, ТЦ МЕГА, Москва', 55.9000, 37.4000, 'Магазин в крупном торговом центре МЕГА. Широкий ассортимент и выгодные цены.', 'Метро: Речной вокзал, далее автобус 851 до МЕГА. Или на автомобиле по МКАД.', '+7 (495) 567-89-01', 'Ежедневно с 10:00 до 23:00')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- ========== ПРИМЕЧАНИЯ ==========
-- 1. Все данные используют ON DUPLICATE KEY UPDATE для безопасного повторного импорта
-- 2. Промокоды активны и имеют различные условия использования
-- 3. Товары связаны с категориями и брендами через внешние ключи
-- 4. Магазины имеют реальные координаты в Москве
-- 5. Пользователей нужно создавать отдельно через интерфейс регистрации или админ-панель

