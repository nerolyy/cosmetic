-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:8889
-- Время создания: Май 14 2026 г., 15:50
-- Версия сервера: 5.7.24
-- Версия PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cosmetic_shop`
--

-- --------------------------------------------------------

--
-- Структура таблицы `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `created_at`) VALUES
(1, 'Dior', 'dior', NULL, '2026-02-09 23:44:55'),
(2, 'Chanel', 'chanel', NULL, '2026-02-09 23:44:55'),
(3, 'Yves Saint Laurent', 'ysl', NULL, '2026-02-09 23:44:55'),
(4, 'MAC', 'mac', NULL, '2026-02-09 23:44:55'),
(5, 'L\'Oreal', 'loreal', NULL, '2026-02-09 23:44:55'),
(6, 'Maybelline', 'maybelline', NULL, '2026-02-09 23:44:55'),
(7, 'Estée Lauder', 'estee-lauder', NULL, '2026-02-09 23:44:55'),
(8, 'Clinique', 'clinique', NULL, '2026-02-09 23:44:55'),
(9, 'Lancome', 'lancome', NULL, '2026-02-09 23:44:55'),
(10, 'NARS', 'nars', NULL, '2026-02-09 23:44:55'),
(11, 'Urban Decay', 'urban-decay', NULL, '2026-02-09 23:44:55'),
(12, 'Too Faced', 'too-faced', NULL, '2026-02-09 23:44:55'),
(20, 'Kérastase', 'kerastase', NULL, '2026-03-17 12:57:24'),
(21, 'Olaplex', 'olaplex', NULL, '2026-03-17 12:57:24'),
(22, 'Moroccanoil', 'moroccanoil', NULL, '2026-03-17 12:57:24'),
(23, 'Batiste', 'batiste', NULL, '2026-03-17 12:57:24'),
(24, 'L\'Oréal Professionnel', 'loreal-professionnel', NULL, '2026-03-17 12:57:24'),
(25, 'OPI', 'opi', NULL, '2026-03-17 12:57:24'),
(26, 'essie', 'essie', NULL, '2026-03-17 12:57:24'),
(27, 'Sally Hansen', 'sally-hansen', NULL, '2026-03-17 12:57:24'),
(28, 'Calvin Klein', 'calvin-klein', NULL, '2026-03-17 12:57:24'),
(29, 'Versace', 'versace', NULL, '2026-03-17 12:57:24'),
(30, 'Giorgio Armani', 'giorgio-armani', NULL, '2026-03-17 12:57:24'),
(31, 'Hugo Boss', 'hugo-boss', NULL, '2026-03-17 12:57:24'),
(32, 'NIVEA MEN', 'nivea-men', NULL, '2026-03-17 12:57:24'),
(33, 'Old Spice', 'old-spice', NULL, '2026-03-17 12:57:24'),
(34, 'Gillette', 'gillette', NULL, '2026-03-17 12:57:24');

-- --------------------------------------------------------

--
-- Структура таблицы `brand_favorites`
--

CREATE TABLE `brand_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(1, 2, 192, 1, '2026-05-11 13:37:43');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_hidden` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `is_hidden`, `created_at`) VALUES
(1, 'Макияж', 'makiyazh', NULL, 0, '2026-02-09 23:44:55'),
(2, 'Парфюмерия', 'parfyumeriya', NULL, 0, '2026-02-09 23:44:55'),
(3, 'Волосы', 'volosy', NULL, 0, '2026-02-09 23:44:55'),
(4, 'Маникюр', 'manikyur', NULL, 0, '2026-02-09 23:44:55'),
(5, 'Мужчины', 'muzhchiny', NULL, 0, '2026-02-09 23:44:55'),
(6, 'Лицо', 'litso', 1, 0, '2026-02-09 23:44:55'),
(7, 'Глаза', 'glaza', 1, 0, '2026-02-09 23:44:55'),
(8, 'Губы', 'guby', 1, 0, '2026-02-09 23:44:55'),
(9, 'Тональные средства', 'tonalnye-sredstva', 6, 0, '2026-02-09 23:44:55'),
(10, 'Пудра', 'pudra', 6, 0, '2026-02-09 23:44:55'),
(11, 'Румяна', 'rumyana', 6, 0, '2026-02-09 23:44:55'),
(12, 'Подводки', 'podvodki', 7, 0, '2026-02-09 23:44:55'),
(13, 'Туши', 'tushi', 7, 0, '2026-02-09 23:44:55'),
(14, 'Тени для ресниц', 'teni-dlya-resnits', 7, 0, '2026-02-09 23:44:55'),
(15, 'Губные помады', 'gubnye-pomady', 8, 0, '2026-02-09 23:44:55'),
(16, 'Блески для губ', 'bleski-dlya-gub', 8, 0, '2026-02-09 23:44:55'),
(17, 'Гигиенические помады', 'gigienicheskie-pomady', 8, 0, '2026-02-09 23:44:55'),
(18, 'Женские ароматы', 'zhenskie-aromaty', 2, 0, '2026-02-09 23:44:55'),
(19, 'Мужские ароматы', 'muzhskie-aromaty', 2, 0, '2026-02-09 23:44:55'),
(20, 'Унисекс ароматы', 'uniseks-aromaty', 2, 0, '2026-02-09 23:44:55'),
(21, 'Сухие шампуни', 'suhie-shampuni', 3, 0, '2026-02-09 23:44:55'),
(22, 'Кондиционеры и бальзамы', 'konditsionery-i-balzamy', 3, 0, '2026-02-09 23:44:55'),
(23, 'Стайлинг', 'stayling', 3, 0, '2026-02-09 23:44:55'),
(24, 'Термозащита', 'termozashchita', 23, 0, '2026-02-09 23:44:55'),
(25, 'Фиксация', 'fiksatsiya', 23, 0, '2026-02-09 23:44:55'),
(26, 'Придание объема', 'pridanie-obema', 23, 0, '2026-02-09 23:44:55'),
(27, 'Средства для снятия лака', 'sredstva-dlya-snyatiya-laka', 4, 0, '2026-02-09 23:44:55'),
(28, 'Гель лаки', 'gel-laki', 4, 0, '2026-02-09 23:44:55'),
(29, 'Лаки для ногтей', 'laki-dlya-nogtey', 4, 0, '2026-02-09 23:44:55'),
(30, 'Волосы', 'volosy-muzhchiny', 5, 0, '2026-02-09 23:44:55'),
(31, 'Бритье', 'britie', 5, 0, '2026-02-09 23:44:55'),
(32, 'Уход', 'uhod-muzhchiny', 5, 0, '2026-02-09 23:44:55'),
(33, 'Шампунь', 'shampun-muzhchiny', 30, 0, '2026-02-09 23:44:55'),
(34, 'Кондиционер', 'konditsioner-muzhchiny', 30, 0, '2026-02-09 23:44:55'),
(35, 'Триммеры и станки', 'trimmery-i-stanki', 31, 0, '2026-02-09 23:44:55'),
(36, 'Средства для бритья', 'sredstva-dlya-britya', 31, 0, '2026-02-09 23:44:55'),
(37, 'Средства после бритья', 'sredstva-posle-britya', 31, 0, '2026-02-09 23:44:55'),
(38, 'Для душа и ванны', 'dlya-dusha-i-vanny', 32, 0, '2026-02-09 23:44:55'),
(39, 'Дезодоранты', 'dezodoranty', 32, 0, '2026-02-09 23:44:55');

-- --------------------------------------------------------

--
-- Структура таблицы `contact_feedback`
--

CREATE TABLE `contact_feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` mediumtext NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `contact_feedback`
--

INSERT INTO `contact_feedback` (`id`, `user_id`, `name`, `email`, `subject`, `body`, `ip`, `created_at`) VALUES
(1, 1, 'admin', 'admin@gmail.com', 'ффф', 'не работает сайт', '::1', '2026-05-14 15:06:14'),
(2, 1, 'admin', '00nrlx@gmail.com', 'dsadasd', 'asdasdsaa', '::1', '2026-05-14 15:09:26');

-- --------------------------------------------------------

--
-- Структура таблицы `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 1, 148, '2026-05-14 15:46:27');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `recipient_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_method` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `shop_id` int(11) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `promo_code_id` int(11) DEFAULT NULL,
  `promo_code_discount` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `recipient_name`, `recipient_phone`, `delivery_method`, `address`, `shop_id`, `delivery_date`, `promo_code_id`, `promo_code_discount`, `created_at`) VALUES
(1, 1, '2190.00', 'pending', 'admin', '79637779168', 'pickup', NULL, 5, '2026-05-15', NULL, '0.00', '2026-05-14 15:23:23'),
(2, 1, '1533.00', 'pending', 'admin', '79637779168', 'pickup', NULL, 4, '2026-05-15', NULL, '657.00', '2026-05-14 15:30:00'),
(3, 1, '2190.00', 'pending', 'admin', '79637779168', 'pickup', NULL, 3, '2026-05-15', NULL, '0.00', '2026-05-14 15:39:57'),
(4, 1, '8592.00', 'pending', 'admin', '79637779168', 'pickup', NULL, 3, '2026-05-15', NULL, '778.00', '2026-05-14 15:47:18'),
(5, 1, '6094.10', 'delivered', 'admin', '79637779168', 'pickup', NULL, 5, '2026-05-15', NULL, '585.90', '2026-05-14 15:49:37');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 150, 1, '2190.00'),
(2, 2, 150, 1, '2190.00'),
(3, 3, 150, 1, '2190.00'),
(4, 4, 148, 1, '3290.00'),
(5, 4, 150, 1, '2190.00'),
(6, 4, 163, 1, '3890.00'),
(7, 5, 163, 1, '3890.00'),
(8, 5, 165, 1, '2790.00');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `discount` int(11) DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT '0',
  `is_new` tinyint(1) DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `old_price`, `discount`, `image`, `category_id`, `brand_id`, `stock`, `is_new`, `is_featured`, `created_at`) VALUES
(147, 'Kérastase Nutritive Lait Vital', 'k-rastase-nutritive-lait-vital-konditsionery-i-balzamy', 'Увлажняющий кондиционер для сухих волос. Делает волосы мягкими и послушными.', '3490.00', '3990.00', 13, '69b968650770a.jpg', 22, 20, 6, 1, 1, '2026-03-17 12:57:24'),
(148, 'Olaplex No.5 Bond Maintenance Conditioner', 'olaplex-no-5-bond-maintenance-conditioner-konditsionery-i-balzamy', 'Кондиционер для восстановления связей. Подходит окрашенным и повреждённым волосам.', '3290.00', NULL, 0, '69b968f26d372.jpg', 22, 21, 8, 0, 1, '2026-03-17 12:57:24'),
(149, 'Moroccanoil Hydrating Conditioner', 'moroccanoil-hydrating-conditioner-konditsionery-i-balzamy', 'Кондиционер с аргановым маслом для увлажнения и блеска.', '2890.00', '3290.00', 12, '69b96a337c745.jpg', 22, 22, 12, 1, 0, '2026-03-17 12:57:24'),
(150, 'L\'Oréal Professionnel Serie Expert Absolut Repair Conditioner', 'l-or-al-professionnel-serie-expert-absolut-repair-conditioner-konditsionery-i-balzamy', 'Питательный кондиционер для восстановления и гладкости.', '2190.00', NULL, 0, '69b96b121ba02.jpg', 22, 24, 11, 0, 0, '2026-03-17 12:57:24'),
(151, 'Moroccanoil Perfect Defense Heat Protectant', 'moroccanoil-perfect-defense-heat-protectant-stayling', 'Термозащита в спрее: защищает от горячих инструментов и добавляет мягкость.', '2890.00', '3290.00', 12, '69b96bb23831f.jpg', 23, 22, 6, 1, 1, '2026-03-17 12:57:24'),
(152, 'L\'Oréal Professionnel Tecni.Art Fix Anti‑Frizz Spray', 'l-or-al-professionnel-tecni-art-fix-anti-frizz-spray-stayling', 'Лак‑спрей для фиксации и защиты от пушистости.', '1490.00', NULL, 0, '69ba9ac5b79f8.jpg', 23, 24, 9, 0, 0, '2026-03-17 12:57:24'),
(153, 'Kérastase Discipline Keratine Thermique', 'k-rastase-discipline-keratine-thermique-stayling', 'Крем‑термозащита для гладкости и дисциплины волос.', '3490.00', '3990.00', 13, '69c680209eaf9.jpg', 23, 20, 12, 0, 1, '2026-03-17 12:57:24'),
(154, 'Batiste Dry Shampoo Original', 'batiste-dry-shampoo-original-suhie-shampuni', 'Сухой шампунь для свежести у корней и объёма за минуту.', '690.00', '890.00', 22, '69c684ef78318.jpg', 21, 23, 6, 1, 1, '2026-03-17 12:57:24'),
(155, 'Batiste Dry Shampoo Blush', 'batiste-dry-shampoo-blush-suhie-shampuni', 'Сухой шампунь с лёгким ароматом, быстро освежает укладку.', '690.00', NULL, 0, '69c685d900c2b.jpg', 21, 23, 9, 0, 0, '2026-03-17 12:57:24'),
(156, 'L\'Oréal Professionnel Tecni.Art Morning After Dust', 'l-or-al-professionnel-tecni-art-morning-after-dust-suhie-shampuni', 'Пудровый сухой шампунь для текстуры и свежести.', '1590.00', '1890.00', 16, '69c6870b69b90.jpg', 21, 24, 12, 0, 1, '2026-03-17 12:57:24'),
(157, 'Dior Diorshow Iconic Overcurl', 'dior-diorshow-iconic-overcurl-glaza', 'Тушь для объёма и подкручивания, выразительный взгляд без комочков.', '3590.00', NULL, 0, '69c68a314b377.jpg', 7, 1, 6, 1, 1, '2026-03-17 12:57:24'),
(158, 'Maybelline Lash Sensational', 'maybelline-lash-sensational-glaza', 'Тушь для веерного объёма и разделения, стойкость на каждый день.', '990.00', '1290.00', 23, '69c68b3d0139a.jpg', 7, 6, 9, 0, 0, '2026-03-17 12:57:24'),
(159, 'Yves Saint Laurent Couture Eyeliner', 'yves-saint-laurent-couture-eyeliner-glaza', 'Жидкая подводка для чёткой стрелки и стойкости.', '3190.00', NULL, 0, '69c68cce03989.jpg', 7, 3, 12, 0, 1, '2026-03-17 12:57:24'),
(160, 'Dior Rouge Dior', 'dior-rouge-dior-guby', 'Классическая помада с комфортной текстурой и насыщенным цветом.', '3990.00', '4390.00', 9, '69c68e81d4cb0.jpg', 8, 1, 6, 1, 1, '2026-03-17 12:57:24'),
(161, 'Chanel Rouge Coco', 'chanel-rouge-coco-guby', 'Увлажняющая помада с сияющим финишем и удобным нанесением.', '4190.00', NULL, 0, '69c690750fa3d.jpg', 8, 2, 9, 0, 1, '2026-03-17 12:57:24'),
(162, 'Maybelline SuperStay Matte Ink', 'maybelline-superstay-matte-ink-guby', 'Стойкая матовая жидкая помада, держится до 16 часов.', '990.00', '1190.00', 17, '69c69339eefc3.jpg', 8, 6, 12, 0, 0, '2026-03-17 12:57:24'),
(163, 'Estée Lauder Double Wear Stay‑in‑Place', 'est-e-lauder-double-wear-stay-in-place-litso', 'Легендарный тональный крем с высокой стойкостью и естественным матовым финишем.', '3890.00', '4290.00', 9, '69c694282185d.jpg', 6, 7, 4, 1, 1, '2026-03-17 12:57:24'),
(164, 'Dior Forever Skin Glow', 'dior-forever-skin-glow-litso', 'Тональный крем с естественным сиянием и стойкостью на весь день.', '4190.00', NULL, 0, '69c694a1e1320.jpg', 6, 1, 9, 0, 1, '2026-03-17 12:57:24'),
(165, 'NARS Radiant Creamy Concealer', 'nars-radiant-creamy-concealer-litso', 'Консилер со средним покрытием и эффектом свежей кожи.', '2790.00', NULL, 0, '69c69510189c6.jpg', 6, 10, 11, 0, 0, '2026-03-17 12:57:24'),
(166, 'MAC Fix+ Setting Spray', 'mac-fix-setting-spray-litso', 'Фиксирующий спрей: освежает макияж и помогает “усадить” пудру.', '2490.00', '2790.00', 11, '69c695fe108ab.jpg', 6, 4, 15, 1, 0, '2026-03-17 12:57:24'),
(167, 'OPI GelColor Bubble Bath', 'opi-gelcolor-bubble-bath-gel-laki', 'Гель‑лак нежного нюдового оттенка. Ровное покрытие и стойкость.', '1690.00', '1990.00', 15, 'seed_opi-gelcolor-bubble-bath-gel-laki.svg', 28, 25, 6, 1, 1, '2026-03-17 12:57:24'),
(168, 'essie Gel Couture Sheer Fantasy', 'essie-gel-couture-sheer-fantasy-gel-laki', 'Гель‑эффект без лампы: чистый цвет и блеск.', '1190.00', NULL, 0, '69c696876adcb.jpg', 28, 26, 9, 0, 0, '2026-03-17 12:57:24'),
(169, 'Sally Hansen Miracle Gel Red Eye', 'sally-hansen-miracle-gel-red-eye-gel-laki', 'Яркий цвет и глянцевый финиш, удобная кисть.', '990.00', '1290.00', 23, '69c696d4b592a.jpg', 28, 27, 12, 0, 0, '2026-03-17 12:57:24'),
(170, 'essie Ballet Slippers', 'essie-ballet-slippers-laki-dlya-nogtey', 'Лак‑классика для аккуратного нюда и “маникюра без ошибок”.', '790.00', '990.00', 20, '69c69751ecf04.jpg', 29, 26, 6, 0, 1, '2026-03-17 12:57:24'),
(171, 'OPI Big Apple Red', 'opi-big-apple-red-laki-dlya-nogtey', 'Классический красный лак с плотным покрытием.', '990.00', NULL, 0, '69c698118aa66.jpg', 29, 25, 9, 1, 0, '2026-03-17 12:57:24'),
(172, 'Sally Hansen Insta‑Dri Quick Dry', 'sally-hansen-insta-dri-quick-dry-laki-dlya-nogtey', 'Быстросохнущий лак для идеального результата за считанные минуты.', '690.00', '890.00', 22, '69c6986bb8b82.jpg', 29, 27, 12, 0, 0, '2026-03-17 12:57:24'),
(173, 'essie Nail Polish Remover', 'essie-nail-polish-remover-sredstva-dlya-snyatiya-laka', 'Средство для снятия лака без пересушивания ногтевой пластины.', '590.00', NULL, 0, '69c6997a45ceb.jpg', 27, 26, 6, 0, 0, '2026-03-17 12:57:24'),
(174, 'OPI Expert Touch Lacquer Remover', 'opi-expert-touch-lacquer-remover-sredstva-dlya-snyatiya-laka', 'Профессиональное средство для снятия лака (ацетоновое).', '790.00', '990.00', 20, '69c699b9e3d80.jpg', 27, 25, 9, 1, 0, '2026-03-17 12:57:24'),
(176, 'Gillette Fusion5 Razor', 'gillette-fusion5-razor-britie', 'Станок с 5 лезвиями для комфортного бритья.', '1490.00', '1790.00', 17, '69c69a957a028.jpg', 31, 34, 6, 0, 1, '2026-03-17 12:57:24'),
(177, 'NIVEA MEN Sensitive Shaving Gel', 'nivea-men-sensitive-shaving-gel-britie', 'Гель для бритья для чувствительной кожи: меньше раздражения.', '490.00', NULL, 0, '69c69ae57de5c.jpg', 31, 32, 9, 1, 0, '2026-03-17 12:57:24'),
(178, 'Old Spice After Shave Lotion', 'old-spice-after-shave-lotion-britie', 'Лосьон после бритья для свежести и комфорта кожи.', '590.00', '690.00', 14, '69c69cebea01d.jpg', 31, 33, 12, 0, 0, '2026-03-17 12:57:24'),
(179, 'L\'Oréal Professionnel Homme Shampoo', 'l-or-al-professionnel-homme-shampoo-volosy-muzhchiny', 'Шампунь для ежедневного очищения и свежести.', '1090.00', NULL, 0, '69c69daba00f0.jpg', 30, 24, 6, 0, 0, '2026-03-17 12:57:24'),
(180, 'NIVEA MEN Shampoo Deep', 'nivea-men-shampoo-deep-volosy-muzhchiny', 'Шампунь с очищающим эффектом, подходит для спорта и города.', '390.00', '490.00', 20, '69c69e4c03578.jpg', 30, 32, 9, 1, 0, '2026-03-17 12:57:24'),
(181, 'Old Spice 2‑in‑1 Shampoo + Conditioner', 'old-spice-2-in-1-shampoo-conditioner-volosy-muzhchiny', 'Удобный 2‑в‑1 формат для дороги и зала.', '490.00', NULL, 0, '69c69e7458b86.jpeg', 30, 33, 12, 0, 1, '2026-03-17 12:57:24'),
(182, 'NIVEA MEN Deodorant Dry Impact', 'nivea-men-deodorant-dry-impact-uhod-muzhchiny', 'Дезодорант‑антиперспирант для защиты и свежести.', '390.00', '490.00', 20, '69c69ecb79dca.jpg', 32, 32, 6, 0, 1, '2026-03-17 12:57:24'),
(183, 'Old Spice Deodorant Stick', 'old-spice-deodorant-stick-uhod-muzhchiny', 'Стик‑дезодорант, классический аромат и комфорт.', '490.00', NULL, 0, '69c69f81cece0.jpg', 32, 33, 9, 1, 0, '2026-03-17 12:57:24'),
(184, 'Gillette Cooling Body Wash', 'gillette-cooling-body-wash-uhod-muzhchiny', 'Гель для душа с охлаждающим эффектом после тренировки.', '590.00', NULL, 0, '69c69ffd01973.jpg', 32, 34, 12, 0, 0, '2026-03-17 12:57:24'),
(185, 'Chanel Coco Mademoiselle Eau de Parfum', 'chanel-coco-mademoiselle-eau-de-parfum-zhenskie-aromaty', 'Классический женственный аромат: цитрусы, роза, пачули.', '11990.00', '13490.00', 11, '69c6a126ab34d.jpg', 18, 2, 6, 1, 1, '2026-03-17 12:57:24'),
(186, 'Dior J’adore Eau de Parfum', 'dior-j-adore-eau-de-parfum-zhenskie-aromaty', 'Цветочный аромат с элегантным шлейфом.', '10990.00', NULL, 0, '69c6a2a2d4cf7.jpg', 18, 1, 9, 0, 1, '2026-03-17 12:57:24'),
(187, 'Versace Bright Crystal Eau de Toilette', 'versace-bright-crystal-eau-de-toilette-zhenskie-aromaty', 'Лёгкий свежий аромат на каждый день.', '6990.00', '7990.00', 13, '69c6a3567c4c2.jpg', 18, 29, 12, 0, 0, '2026-03-17 12:57:24'),
(188, 'Dior Sauvage Eau de Parfum', 'dior-sauvage-eau-de-parfum-muzhskie-aromaty', 'Древесно‑ароматический мужской бестселлер.', '10490.00', '11990.00', 13, '69c6a3e42ef00.jpg', 19, 1, 6, 1, 1, '2026-03-17 12:57:24'),
(189, 'Giorgio Armani Acqua di Giò Eau de Toilette', 'giorgio-armani-acqua-di-gi-eau-de-toilette-muzhskie-aromaty', 'Свежий морской аромат: цитрусы и древесные ноты.', '8990.00', NULL, 0, '69c6a4abe8885.jpg', 19, 30, 9, 0, 1, '2026-03-17 12:57:24'),
(190, 'Hugo Boss BOSS Bottled Eau de Toilette', 'hugo-boss-boss-bottled-eau-de-toilette-muzhskie-aromaty', 'Классический аромат: яблоко, специи, древесные ноты.', '7490.00', '8490.00', 12, '69c6a50699a5e.jpg', 19, 31, 12, 0, 0, '2026-03-17 12:57:24'),
(191, 'Calvin Klein CK One Eau de Toilette', 'calvin-klein-ck-one-eau-de-toilette-uniseks-aromaty', 'Культовый унисекс‑аромат: свежий, чистый, повседневный.', '4990.00', '5990.00', 17, '69c6a5a9ef2e5.jpg', 20, 28, 6, 0, 1, '2026-03-17 12:57:24'),
(192, 'Yves Saint Laurent Libre Eau de Parfum', 'yves-saint-laurent-libre-eau-de-parfum-uniseks-aromaty', 'Современный аромат с лавандой и ванилью (универсально, со шлейфом).', '10990.00', NULL, 0, '69c6a658c171f.jpg', 20, 3, 9, 1, 0, '2026-03-17 12:57:24'),
(193, 'Chanel Chance Eau Tendre', 'chanel-chance-eau-tendre-uniseks-aromaty', 'Свежий цветочно‑фруктовый аромат, лёгкий и деликатный.', '9990.00', '11290.00', 12, '69c6a6a87b175.jpg', 20, 2, 12, 0, 0, '2026-03-17 12:57:24');

-- --------------------------------------------------------

--
-- Структура таблицы `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT '0.00',
  `max_order_amount` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `current_uses` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `valid_from` datetime DEFAULT CURRENT_TIMESTAMP,
  `valid_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_order_amount`, `max_discount`, `max_uses`, `current_uses`, `is_active`, `valid_from`, `valid_until`, `created_at`, `updated_at`) VALUES
(1, 'FIRST50', 'Скидка 50% на первый заказ', 'percentage', '50.00', '0.00', NULL, '2000.00', 100, 0, 1, '2026-02-10 02:44:55', '2027-02-10 02:44:55', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(2, 'WELCOME10', 'Скидка 10% для новых клиентов', 'percentage', '10.00', '1000.00', NULL, NULL, 500, 0, 1, '2026-02-10 02:44:55', '2026-08-10 02:44:55', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(3, 'SUMMER20', 'Летняя скидка 20%', 'percentage', '20.00', '2000.00', NULL, '3000.00', 200, 0, 1, '2026-02-10 02:44:55', '2026-05-10 02:44:55', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(4, 'FIXED500', 'Скидка 500 рублей', 'fixed', '500.00', '3000.00', NULL, NULL, 150, 0, 1, '2026-02-10 02:44:55', '2027-02-10 02:44:55', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(5, 'NEWYEAR30', 'Новогодняя скидка 30%', 'percentage', '30.00', '1500.00', NULL, '5000.00', 300, 0, 1, '2026-02-10 02:44:55', '2026-04-10 02:44:55', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(6, 'VIP15', 'Скидка 15% для постоянных клиентов', 'percentage', '15.00', '2500.00', NULL, NULL, NULL, 0, 1, '2026-02-10 02:44:55', NULL, '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(7, 'BIRTHDAY25', 'Скидка 25% в день рождения', 'percentage', '25.00', '1000.00', NULL, '2000.00', NULL, 0, 1, '2026-02-10 02:44:55', NULL, '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(8, 'FREESHIP', 'Бесплатная доставка', 'fixed', '0.00', '5000.00', NULL, NULL, 1000, 0, 1, '2026-02-10 02:44:55', '2027-02-10 02:44:55', '2026-02-09 23:44:55', '2026-02-09 23:44:55');

-- --------------------------------------------------------

--
-- Структура таблицы `promo_code_uses`
--

CREATE TABLE `promo_code_uses` (
  `id` int(11) NOT NULL,
  `promo_code_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `shops`
--

CREATE TABLE `shops` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Название магазина',
  `address` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Адрес магазина',
  `latitude` decimal(10,8) NOT NULL COMMENT 'Широта (координата Y)',
  `longitude` decimal(11,8) NOT NULL COMMENT 'Долгота (координата X)',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Описание магазина',
  `how_to_get` text COLLATE utf8mb4_unicode_ci COMMENT 'Как добраться до магазина',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Телефон магазина',
  `work_hours` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Режим работы',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Магазины';

--
-- Дамп данных таблицы `shops`
--

INSERT INTO `shops` (`id`, `name`, `address`, `latitude`, `longitude`, `description`, `how_to_get`, `phone`, `work_hours`, `created_at`, `updated_at`) VALUES
(1, 'Косметика на Красной площади', 'ул. Никольская, д. 15, Москва', '55.75580000', '37.61730000', 'Флагманский магазин косметики в центре Москвы. Широкий ассортимент премиальных брендов.', 'Метро: Охотный ряд, Площадь Революции. Выход к Красной площади.', '+7 (495) 123-45-67', 'Ежедневно с 10:00 до 22:00', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(2, 'Косметика на Тверской', 'ул. Тверская, д. 8, Москва', '55.75580000', '37.61000000', 'Магазин косметики в центре города. Удобное расположение, профессиональные консультанты.', 'Метро: Тверская, Пушкинская. 5 минут пешком от метро.', '+7 (495) 234-56-78', 'Пн-Сб: 10:00-21:00, Вс: 11:00-20:00', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(3, 'Косметика в ТЦ Авиапарк', 'Ходынский бульвар, д. 4, ТЦ Авиапарк, Москва', '55.78900000', '37.53000000', 'Магазин в крупном торговом центре. Большой выбор товаров, акции и скидки.', 'Метро: Динамо, Сокол. Автобусы 105, 110 до остановки Авиапарк.', '+7 (495) 345-67-89', 'Ежедневно с 10:00 до 22:00', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(4, 'Косметика на Арбате', 'ул. Арбат, д. 45, Москва', '55.75200000', '37.59200000', 'Уютный магазин на знаменитой улице Арбат. Индивидуальный подход к каждому клиенту.', 'Метро: Арбатская, Смоленская. Пешком по Арбату.', '+7 (495) 456-78-90', 'Пн-Пт: 11:00-20:00, Сб-Вс: 10:00-21:00', '2026-02-09 23:44:55', '2026-02-09 23:44:55'),
(5, 'Косметика в МЕГА', 'Химки, МКАД 66-й км, ТЦ МЕГА, Москва', '55.90000000', '37.40000000', 'Магазин в крупном торговом центре МЕГА. Широкий ассортимент и выгодные цены.', 'Метро: Речной вокзал, далее автобус 851 до МЕГА. Или на автомобиле по МКАД.', '+7 (495) 567-89-01', 'Ежедневно с 10:00 до 23:00', '2026-02-09 23:44:55', '2026-02-09 23:44:55');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `name`, `phone`, `created_at`) VALUES
(1, '00nrlx@gmail.com', '$2y$10$29AgZCUIXEpl4OL7iWE5k.D3WFoj52BjpZDAmGwduk8Va7.02mujG', 'admin', 'admin', '+79637779168', '2026-02-10 11:06:52'),
(2, 'jonsonsins111@gmail.com', '$2y$10$dbLoq.uzrRWokgSitOHbmOBQrGJC3LwIA.JLBHQVzJ9nFFMS1d9rO', 'user', 'Artem', '89637779168', '2026-04-29 14:16:53'),
(3, 'ancientnuke@mail.ru', '$2y$10$GnpeekOc8Qy8gkBuR0bFEem6cKEQZmnxXBCiKio186PH35XUplXhS', 'user', 'Пользователь', '89637779168', '2026-05-14 14:46:52'),
(4, 'artemmyagkov97@mail.ru', '$2y$10$MfmMzqQGez9MwD4UYVjs8.pS7EqHwy1bls99PwRNM4zpkolzDHZHS', 'user', 'Olise', '89637779168', '2026-05-14 14:52:48');

-- --------------------------------------------------------

--
-- Структура таблицы `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID пользователя',
  `shop_id` int(11) DEFAULT NULL COMMENT 'ID любимого магазина (если выбран самовывоз)',
  `delivery_address` text COLLATE utf8mb4_unicode_ci COMMENT 'Адрес для доставки курьером',
  `is_default` tinyint(1) DEFAULT '0' COMMENT 'Адрес по умолчанию',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Адреса пользователей';

-- --------------------------------------------------------

--
-- Структура таблицы `user_wheel_spins`
--

CREATE TABLE `user_wheel_spins` (
  `user_id` int(11) NOT NULL,
  `spins` int(11) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `user_wheel_spins`
--

INSERT INTO `user_wheel_spins` (`user_id`, `spins`, `updated_at`) VALUES
(1, 30, '2026-05-14 15:49:37'),
(2, 9, '2026-05-11 13:56:49'),
(3, 3, '2026-05-14 14:46:52'),
(4, 3, '2026-05-14 14:52:48');

-- --------------------------------------------------------

--
-- Структура таблицы `wheel_rewards`
--

CREATE TABLE `wheel_rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_type` varchar(32) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_name` varchar(255) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `promo_code` varchar(32) NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `wheel_rewards`
--

INSERT INTO `wheel_rewards` (`id`, `user_id`, `reward_type`, `target_id`, `target_name`, `discount_percent`, `promo_code`, `is_used`, `expires_at`, `created_at`) VALUES
(1, 2, 'category', 10, 'Пудра', 18, 'WHEEL9AE71A', 0, '2026-05-18 13:33:27', '2026-05-11 13:33:27'),
(2, 2, 'brand', 9, 'Lancome', 13, 'WHEELA7EF82', 0, '2026-05-18 13:34:07', '2026-05-11 13:34:07'),
(3, 2, 'brand', 3, 'Yves Saint Laurent', 13, 'WHEEL907387', 0, '2026-05-18 13:37:23', '2026-05-11 13:37:23'),
(4, 2, 'category', 6, 'Лицо', 14, 'WHEELBA2032', 0, '2026-05-18 13:48:21', '2026-05-11 13:48:21'),
(5, 2, 'brand', 6, 'Maybelline', 24, 'WHEELE16558', 0, '2026-05-18 13:49:36', '2026-05-11 13:49:36'),
(6, 2, 'category', 6, 'Лицо', 15, 'WHEELF2D46C', 0, '2026-05-18 13:49:44', '2026-05-11 13:49:44'),
(7, 2, 'brand', 33, 'Old Spice', 21, 'WHEELCB8B5A', 0, '2026-05-18 13:49:54', '2026-05-11 13:49:54'),
(8, 2, 'category', 31, 'Бритье', 8, 'WHEELD1FBE2', 0, '2026-05-18 13:50:02', '2026-05-11 13:50:02'),
(9, 2, 'brand', 27, 'Sally Hansen', 10, 'WHEEL4C7DE8', 0, '2026-05-18 13:50:10', '2026-05-11 13:50:10'),
(10, 2, 'brand', 21, 'Olaplex', 14, 'WHEELDDC12E', 0, '2026-05-18 13:50:16', '2026-05-11 13:50:16'),
(11, 2, 'brand', 12, 'Too Faced', 24, 'WHEELA4CAB7', 0, '2026-05-18 13:52:02', '2026-05-11 13:52:02'),
(12, 2, 'brand', 4, 'MAC', 18, 'WHEELA355D3', 0, '2026-05-18 13:53:34', '2026-05-11 13:53:34'),
(13, 2, 'brand', 25, 'OPI', 15, 'WHEEL64DDFF', 0, '2026-05-18 13:53:39', '2026-05-11 13:53:39'),
(14, 2, 'category', 20, 'Унисекс ароматы', 11, 'WHEELE79525', 0, '2026-05-18 13:53:41', '2026-05-11 13:53:41'),
(15, 2, 'product', 159, 'Yves Saint Laurent Couture Eyeliner', 24, 'WHEELF64E0C', 0, '2026-05-18 13:53:46', '2026-05-11 13:53:46'),
(16, 2, 'product', 188, 'Dior Sauvage Eau de Parfum', 22, 'WHEELCE3C2A', 0, '2026-05-18 13:55:00', '2026-05-11 13:55:00'),
(17, 2, 'product', 147, 'Kérastase Nutritive Lait Vital', 29, 'WHEELDBA8D6', 0, '2026-05-18 13:55:03', '2026-05-11 13:55:03'),
(18, 2, 'category', 5, 'Мужчины', 18, 'WHEEL941D10', 0, '2026-05-18 13:55:09', '2026-05-11 13:55:09'),
(19, 2, 'product', 176, 'Gillette Fusion5 Razor', 28, 'WHEEL7FB87E', 0, '2026-05-18 13:56:49', '2026-05-11 13:56:49'),
(20, 1, 'product', 150, 'L\'Oréal Professionnel Serie Expert Absolut Repair Conditioner', 30, 'WHEELEA4F8F', 1, '2026-05-21 15:19:11', '2026-05-14 15:19:11'),
(21, 1, 'category', 6, 'Лицо', 20, 'WHEEL2BAFDD', 1, '2026-05-21 15:46:54', '2026-05-14 15:46:54'),
(22, 1, 'brand', 10, 'NARS', 21, 'WHEEL8602BB', 1, '2026-05-21 15:49:18', '2026-05-14 15:49:18');

-- --------------------------------------------------------

--
-- Структура таблицы `wheel_spin_history`
--

CREATE TABLE `wheel_spin_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `spin_delta` int(11) NOT NULL,
  `reason` varchar(64) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `wheel_spin_history`
--

INSERT INTO `wheel_spin_history` (`id`, `user_id`, `spin_delta`, `reason`, `order_id`, `created_at`) VALUES
(1, 2, 3, 'registration_bonus', NULL, '2026-05-11 13:33:20'),
(2, 2, -1, 'spin_used', NULL, '2026-05-11 13:33:27'),
(3, 2, -1, 'spin_used', NULL, '2026-05-11 13:34:07'),
(4, 2, -1, 'spin_used', NULL, '2026-05-11 13:37:23'),
(5, 2, 15, 'manual_bonus_15', NULL, '2026-05-11 13:48:16'),
(6, 2, -1, 'spin_used', NULL, '2026-05-11 13:48:21'),
(7, 2, -1, 'spin_used', NULL, '2026-05-11 13:49:36'),
(8, 2, -1, 'spin_used', NULL, '2026-05-11 13:49:44'),
(9, 2, -1, 'spin_used', NULL, '2026-05-11 13:49:54'),
(10, 2, -1, 'spin_used', NULL, '2026-05-11 13:50:02'),
(11, 2, -1, 'spin_used', NULL, '2026-05-11 13:50:10'),
(12, 2, -1, 'spin_used', NULL, '2026-05-11 13:50:16'),
(13, 2, -1, 'spin_used', NULL, '2026-05-11 13:52:02'),
(14, 2, -1, 'spin_used', NULL, '2026-05-11 13:53:34'),
(15, 2, -1, 'spin_used', NULL, '2026-05-11 13:53:39'),
(16, 2, -1, 'spin_used', NULL, '2026-05-11 13:53:41'),
(17, 2, -1, 'spin_used', NULL, '2026-05-11 13:53:46'),
(18, 2, -1, 'spin_used', NULL, '2026-05-11 13:55:00'),
(19, 2, -1, 'spin_used', NULL, '2026-05-11 13:55:03'),
(20, 2, -1, 'spin_used', NULL, '2026-05-11 13:55:09'),
(21, 2, 10, 'manual_bonus_10', NULL, '2026-05-11 13:56:47'),
(22, 2, -1, 'spin_used', NULL, '2026-05-11 13:56:49'),
(23, 3, 3, 'registration_bonus', NULL, '2026-05-14 14:46:52'),
(24, 4, 3, 'registration_bonus', NULL, '2026-05-14 14:52:49'),
(25, 1, 3, 'registration_bonus', NULL, '2026-05-14 15:14:17'),
(26, 1, 15, 'manual_bonus_15', NULL, '2026-05-14 15:14:17'),
(27, 1, 10, 'manual_bonus_10', NULL, '2026-05-14 15:14:17'),
(28, 1, -1, 'spin_used', NULL, '2026-05-14 15:19:11'),
(29, 1, 1, 'order_over_1000', 1, '2026-05-14 15:23:23'),
(30, 1, 1, 'order_over_1000', 2, '2026-05-14 15:30:00'),
(31, 1, 1, 'order_over_1000', 3, '2026-05-14 15:39:57'),
(32, 1, -1, 'spin_used', NULL, '2026-05-14 15:46:54'),
(33, 1, 1, 'order_over_1000', 4, '2026-05-14 15:47:18'),
(34, 1, -1, 'spin_used', NULL, '2026-05-14 15:49:18'),
(35, 1, 1, 'order_over_1000', 5, '2026-05-14 15:49:37');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `brand_favorites`
--
ALTER TABLE `brand_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_brand_favorite` (`user_id`,`brand_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Индексы таблицы `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `cart_product_id_fk` (`product_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_hidden` (`is_hidden`);

--
-- Индексы таблицы `contact_feedback`
--
ALTER TABLE `contact_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_feedback_created` (`created_at`);

--
-- Индексы таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_promo_code` (`promo_code_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_brand` (`brand_id`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_new` (`is_new`);

--
-- Индексы таблицы `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_valid_dates` (`valid_from`,`valid_until`);

--
-- Индексы таблицы `promo_code_uses`
--
ALTER TABLE `promo_code_uses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_promo_code` (`promo_code_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Индексы таблицы `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coordinates` (`latitude`,`longitude`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Индексы таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Индексы таблицы `user_wheel_spins`
--
ALTER TABLE `user_wheel_spins`
  ADD PRIMARY KEY (`user_id`);

--
-- Индексы таблицы `wheel_rewards`
--
ALTER TABLE `wheel_rewards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promo_code` (`promo_code`),
  ADD KEY `idx_wheel_rewards_user` (`user_id`);

--
-- Индексы таблицы `wheel_spin_history`
--
ALTER TABLE `wheel_spin_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wheel_history_user` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT для таблицы `brand_favorites`
--
ALTER TABLE `brand_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT для таблицы `contact_feedback`
--
ALTER TABLE `contact_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT для таблицы `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `promo_code_uses`
--
ALTER TABLE `promo_code_uses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `shops`
--
ALTER TABLE `shops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `wheel_rewards`
--
ALTER TABLE `wheel_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `wheel_spin_history`
--
ALTER TABLE `wheel_spin_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `brand_favorites`
--
ALTER TABLE `brand_favorites`
  ADD CONSTRAINT `brand_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brand_favorites_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_product_id_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `promo_code_uses`
--
ALTER TABLE `promo_code_uses`
  ADD CONSTRAINT `promo_code_uses_ibfk_1` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promo_code_uses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promo_code_uses_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_addresses_ibfk_2` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `user_wheel_spins`
--
ALTER TABLE `user_wheel_spins`
  ADD CONSTRAINT `fk_user_wheel_spins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `wheel_rewards`
--
ALTER TABLE `wheel_rewards`
  ADD CONSTRAINT `fk_wheel_rewards_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `wheel_spin_history`
--
ALTER TABLE `wheel_spin_history`
  ADD CONSTRAINT `fk_wheel_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
