-- ====================================================================
-- ZOUZA SHOP - JEU DE DONNEES INITIAL / DE TEST (SEED)
-- ====================================================================
-- ATTENTION:
-- Ce fichier contient des donnees factices destinees au developpement local.
-- Aucun mot de passe reel n'est inclus.
-- Le compte administrateur ci-dessous utilise le mot de passe de test : 'admin123'
-- Hash genere via PHP : password_hash('admin123', PASSWORD_DEFAULT)
-- ====================================================================

USE `zouza_shop`;

-- 1. Nettoyage preventif des donnees
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Insertion du compte administrateur de developpement
INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `telephone`, `password`, `role`, `created_at`)
VALUES
(1, 'Admin', 'Zouza', 'admin@zouza.local', '+216 20 000 000', '$2y$10$Y585wdcaaKWNHTNAbsscoeTdbqVIEtHbzqR4S1kGMYEzS5g9qJcja', 'admin', NOW());

-- 3. Insertion des categories de patisseries
INSERT INTO `categories` (`id`, `nom`, `slug`, `description`, `created_at`)
VALUES
(1, 'Zouza Traditionnelle', 'zouza-traditionnelle', 'La recette authentique tunisienne garnie au caramel fondant et fruits secs.', NOW()),
(2, 'Zouza Gourmande', 'zouza-gourmande', 'Variations modernes et genereuses au chocolat, noisette et pistache.', NOW()),
(3, 'Coffrets & Plateaux', 'coffrets-plateaux', 'Assortiments elegants pour vos fetes, mariages et cadeaux gourmands.', NOW());

-- 4. Insertion de produits Zouza
INSERT INTO `products` (`id`, `category_id`, `nom`, `slug`, `description`, `prix`, `image`, `disponible`, `created_at`)
VALUES
(1, 1, 'Zouza Classique Caramel', 'zouza-classique-caramel', 'Boite de 500g de Zouza traditionnelle garnie au caramel maison fondant.', 18.500, 'zouza-caramel.jpg', 1, NOW()),
(2, 1, 'Zouza Caramel & Amande', 'zouza-caramel-amande', 'Boite de 500g de Zouza fourree au caramel fondant et amandes grillees croquantes.', 21.000, 'zouza-amande.jpg', 1, NOW()),
(3, 2, 'Zouza Chocolat & Noisette', 'zouza-chocolat-noisette', 'Boite de 500g de Zouza au coeur praline chocolat noir et noisettes entieres.', 23.500, 'zouza-chocolat-noisette.jpg', 1, NOW()),
(4, 2, 'Zouza Creme de Pistache', 'zouza-creme-pistache', 'Boite de 500g de Zouza garnie d une creme onctueuse de pistache pure.', 27.000, 'zouza-pistache.jpg', 1, NOW()),
(5, 3, 'Coffret Prestige Assorti (1 kg)', 'coffret-prestige-assorti', 'Plateau cadeau luxe de 1 kg regroupant nos meilleures varietes de Zouza.', 48.000, 'coffret-prestige.jpg', 1, NOW()),
(6, 3, 'Mini Box Decouverte (250g)', 'mini-box-decouverte', 'Format ideal pour deguster ou offrir une pause sucree authentique.', 11.500, 'mini-box.jpg', 1, NOW());

-- 5. Insertion d une commande test (guest checkout)
INSERT INTO `orders` (`id`, `reference`, `user_id`, `nom_client`, `prenom_client`, `telephone`, `email`, `adresse`, `ville`, `type_livraison`, `total`, `statut`, `notes`, `date_commande`)
VALUES
(1, 'ZS-2026-00001', NULL, 'Ben Ali', 'Sarra', '+216 98 123 456', 'sarra.benali@example.com', 'Avenue Habib Bourguiba', 'Tunis', 'livraison', 42.000, 'confirmed', 'Merci de livrer apres 15h.', NOW());

-- 6. Lignes de la commande test
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantite`, `prix_unitaire`, `sous_total`)
VALUES
(1, 1, 1, 1, 18.500, 18.500),
(2, 1, 3, 1, 23.500, 23.500);
