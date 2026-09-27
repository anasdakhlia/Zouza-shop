# Base de Données - Zouza Shop

Ce dossier contient les scripts SQL pour initialiser la base de données du projet **Zouza Shop**.

---

## 1. Vue d'ensemble du Schéma

La base de données `zouza_shop` repose sur le moteur **InnoDB** avec encodage **utf8mb4_unicode_ci**.

Elle est composée de 5 tables principales avec des clés primaires explicites :

1. **`users`** : Clé primaire `user_id`. Comptes administrateurs et clients inscrits (mots de passe hashés avec `password_hash()`).
2. **`categories`** : Clé primaire `category_id`. Catégories de pâtisseries Zouza avec un `slug` unique pour les URLs.
3. **`products`** : Clé primaire `product_id`. Produits vendus, liés à `categories(category_id)` (`ON DELETE RESTRICT`).
4. **`orders`** : Clé primaire `order_id`. Commandes clients, avec support du checkout invité (`user_id` est `NULL` si le client n'a pas de compte).
5. **`order_items`** : Clé primaire `order_item_id`. Lignes d'articles liées à `orders(order_id)` et `products(product_id)`, avec le prix unitaire et sous-total figés au moment de l'achat.

---

## 2. Choix d'Architecture Importants

### Conventions de Nommage des Clés Primaires
- Chaque clé primaire porte le nom explicite de son entité (`category_id`, `product_id`, `user_id`, `order_id`, `order_item_id`).
- Cela simplifie les requêtes SQL, évite toute ambiguïté lors des jointures (`JOIN ... USING (category_id)`), et facilite la lisibilité du code MVC.

### Précision Monétaire : `DECIMAL(10, 3)`
- Le dinar tunisien (TND) utilise 3 décimales (les millimes : ex. `18.500 TND`).
- Les types à virgule flottante (`FLOAT`, `DOUBLE`) sont **strictement évités** pour empêcher les erreurs d'arrondi binaires inhérentes à la norme IEEE 754.
- Le type `DECIMAL(10, 3)` garantit des calculs financiers exacts à la fois en base de données et en PHP.

### Préservation de l'Historique Financier
- La table `order_items` enregistre explicitement `prix_unitaire` et `sous_total` à la validation du panier.
- Toute modification ultérieure du prix d'un produit dans la table `products` n'impacte pas les commandes passées.

### Checkout Invité (Guest Checkout)
- La colonne `user_id` de la table `orders` est `NULLable`.
- En cas de commande sans compte, les informations de contact et de livraison (`nom_client`, `prenom_client`, `telephone`, `email`, `adresse`, `ville`) sont stockées directement dans la table `orders`.
- Si un utilisateur inscrit supprime son compte (`ON DELETE SET NULL`), ses commandes restent intactes pour la comptabilité du commerce.

### Protection contre la Suppression Accidentelle de Produits
- La clé étrangère `order_items.product_id` utilise `ON DELETE RESTRICT`. Un produit ayant déjà fait l'objet d'une commande ne peut pas être supprimé par erreur de la base ; il doit être désactivé via le champ `disponible = 0`.

---

## 3. Guide d'Importation

### Méthode 1 : Via phpMyAdmin (Interface Web XAMPP)
1. Démarrer **Apache** et **MySQL** dans le panneau de contrôle XAMPP.
2. Ouvrir le navigateur à l'adresse : `http://localhost/phpmyadmin/`.
3. Cliquer sur l'onglet **Importer**.
4. Sélectionner le fichier `database/schema.sql` et valider (la base `zouza_shop` et ses tables seront créées).
5. Répéter l'opération pour importer `database/seed.sql` si vous souhaitez charger les données de test.

### Méthode 2 : Via la ligne de commande MySQL (XAMPP)
```bash
# Importer le schéma
C:\xampp\mysql\bin\mysql.exe -u root < "c:\freelance\projet zouzza\zouza-shop\database\schema.sql"

# Charger les données de test (optionnel)
C:\xampp\mysql\bin\mysql.exe -u root < "c:\freelance\projet zouzza\zouza-shop\database\seed.sql"
```

---

## 4. Données de Test (Seed)

- **Compte administrateur local :**
  - Email : `admin@zouza.local`
  - Mot de passe de test : `admin123` (hashé avec `PASSWORD_DEFAULT`)
- **Catégories :** Zouza Traditionnelle, Zouza Gourmande, Coffrets & Plateaux.
- **Produits :** 6 variantes de Zouza prêtes à l'emploi.
