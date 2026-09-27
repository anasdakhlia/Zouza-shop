-- ====================================================================
-- ZOUZA SHOP - SCHEMA DE LA BASE DE DONNEES
-- Version: 1.1.0 (Cles primaires explicites : table_id)
-- Moteur: MySQL 8.x / MariaDB (XAMPP)
-- Jeu de caracteres: utf8mb4 / utf8mb4_unicode_ci
-- ====================================================================

-- 1. Creation de la base de donnees (si execute en tant que root)
CREATE DATABASE IF NOT EXISTS `zouza_shop`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `zouza_shop`;

-- Desactivation temporaire des verifications de cles etrangeres
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================================================
-- 2. SUPPRESSION PREVENTIVE DES TABLES (ORDRE INVERSE DES DEPENDANCES)
-- ====================================================================
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ====================================================================
-- 3. TABLE: USERS (Utilisateurs et Administrateurs)
-- ====================================================================
CREATE TABLE `users` (
    `user_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `telephone` VARCHAR(30) NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 4. TABLE: CATEGORIES (Categories de patisseries Zouza)
-- ====================================================================
CREATE TABLE `categories` (
    `category_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_categories_nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 5. TABLE: PRODUCTS (Produits / Variantes de Zouza)
-- ====================================================================
CREATE TABLE `products` (
    `product_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `nom` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(180) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `prix` DECIMAL(10,3) NOT NULL,
    `image` VARCHAR(255) NULL,
    `disponible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category`
        FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`category_id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    INDEX `idx_products_category_id` (`category_id`),
    INDEX `idx_products_disponible` (`disponible`),
    INDEX `idx_products_nom` (`nom`),
    CONSTRAINT `chk_products_prix_positive` CHECK (`prix` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 6. TABLE: ORDERS (Commandes clients - supporte le checkout invite)
-- ====================================================================
CREATE TABLE `orders` (
    `order_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `reference` VARCHAR(40) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED NULL,
    `nom_client` VARCHAR(100) NOT NULL,
    `prenom_client` VARCHAR(100) NOT NULL,
    `telephone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `adresse` TEXT NULL,
    `ville` VARCHAR(100) NULL,
    `type_livraison` ENUM('livraison', 'a_emporter') NOT NULL DEFAULT 'livraison',
    `total` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    `statut` ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    `notes` TEXT NULL,
    `date_commande` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`user_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX `idx_orders_user_id` (`user_id`),
    INDEX `idx_orders_statut` (`statut`),
    INDEX `idx_orders_date_commande` (`date_commande`),
    CONSTRAINT `chk_orders_total_positive` CHECK (`total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 7. TABLE: ORDER_ITEMS (Lignes de commande avec prix fige)
-- ====================================================================
CREATE TABLE `order_items` (
    `order_item_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantite` INT UNSIGNED NOT NULL DEFAULT 1,
    `prix_unitaire` DECIMAL(10,3) NOT NULL,
    `sous_total` DECIMAL(10,3) NOT NULL,
    CONSTRAINT `fk_order_items_order`
        FOREIGN KEY (`order_id`)
        REFERENCES `orders` (`order_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_product`
        FOREIGN KEY (`product_id`)
        REFERENCES `products` (`product_id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    INDEX `idx_order_items_order_id` (`order_id`),
    INDEX `idx_order_items_product_id` (`product_id`),
    CONSTRAINT `chk_order_items_quantite_positive` CHECK (`quantite` > 0),
    CONSTRAINT `chk_order_items_prix_positive` CHECK (`prix_unitaire` >= 0),
    CONSTRAINT `chk_order_items_sous_total_positive` CHECK (`sous_total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
