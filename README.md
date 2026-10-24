# Zouza Shop 🌰 — Pâtisserie Artisanale Tunisienne

Site e-commerce complet de commande en ligne pour une pâtisserie artisanale tunisienne spécialisée dans les **Zouzas** traditionnelles et contemporaines (fourrées au caramel beurre salé, chocolat noisette, amande, pistache, etc.).

Le projet est développé en **PHP 8 (Architecture MVC pure, sans framework lourd)** avec MySQL/MariaDB, HTML5, CSS3, JavaScript Vanilla et Bootstrap 5.

---

## 1. Fonctionnalités Principales

### 🧁 Front Office (Clients & Visiteurs)
- **Catalogue & Navigation** : Consultation des délices par catégorie, recherche textuelle en temps réel, filtres de disponibilité et tri par prix/nouveauté.
- **Fiches Produits Détaillées** : Visuels, description des ingrédients, statut de confection et prix précis en Dinars Tunisiens (TND).
- **Panier d'Achat Session** : Ajout, modification des quantités, suppression d'articles et calcul instantané des totaux sans rechargement intempestif.
- **Tunnel de Commande (Checkout)** :
  - Commande avec compte client existant.
  - Commande invitée (*Guest Checkout*) sans obligation de création de compte.
- **Espace Client Sécurisé** : Historique des commandes, détail des articles commandés, gestion des coordonnées et changement de mot de passe sécurisé.

### 🛡️ Back Office (Administration)
- **Tableau de Bord** : Vue synthétique des métriques clés (chiffre d'affaires, volume de commandes, nouveaux clients, produits phares).
- **Gestion du Catalogue** : Création, modification, upload d'images et désactivation/archivage des délices et des catégories.
- **Suivi des Commandes** : Consultation détaillée de chaque commande (coordonnées, adresse de livraison, lignes d'articles figées) et mise à jour du statut (`en_attente`, `confirmee`, `en_preparation`, `expediee`, `livree`, `annulee`).
- **Sécurité Renforcée** : Contrôle d'accès basé sur les rôles (`role = 'admin'`), balises `noindex, nofollow` sur toutes les pages d'administration.

---

## 2. Prérequis Système

| Composant | Version Requise | Recommandation |
| :--- | :--- | :--- |
| **PHP** | `>= 8.1` (testé sous PHP 8.2) | PHP 8.2+ |
| **SGBD** | MySQL `>= 5.7` ou MariaDB `>= 10.4` | MariaDB 10.4+ / MySQL 8.0 |
| **Serveur Web** | Apache `>= 2.4` avec module `mod_rewrite` activé | Apache 2.4+ (XAMPP / LAMP) |
| **Extensions PHP** | `pdo_mysql`, `mbstring`, `xmlwriter`, `curl`, `json` | Incluses par défaut dans XAMPP |

---

## 3. Installation Locale avec XAMPP (Windows)

### Étape 1 : Emplacement du Projet
Placez ou clonez le projet dans le répertoire `htdocs` de votre installation XAMPP :
```bash
# Exemple d'arborescence :
C:\xampp\htdocs\zouza-shop
```

### Étape 2 : Configuration de l'Environnement Local
L'application utilise un système de configuration hiérarchique :
1. `config/config.php` : Configuration générale et valeurs par défaut pour XAMPP.
2. `config/config.local.php` : Surcharge locale pour vos identifiants réels (ce fichier est **exclu de Git** pour protéger les secrets).

Pour personnaliser vos accès, copiez le modèle fourni :
```powershell
Copy-Item config\config.example.php config\config.local.php
```
Puis éditez `config/config.local.php` si vos paramètres MySQL diffèrent du standard XAMPP :
```php
<?php
// Exemple pour un port MySQL personnalisé ou un mot de passe non vide :
define('APP_ENV', 'development');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'zouza_shop');
define('DB_USER', 'root');
define('DB_PASS', '');
```

> [!IMPORTANT]
> Ne commitez **jamais** de mots de passe, clés secrètes ou identifiants de base de données dans Git. Le fichier `config/config.local.php` est protégé par `.gitignore`.

---

## 4. Initialisation de la Base de Données

> [!WARNING]
> Respectez l'ordre d'importation des scripts SQL. Ne jamais exécuter de commandes destructives (`DROP DATABASE`, `TRUNCATE`) sur un environnement contenant des commandes réelles.

### Méthode A : Interface Graphique phpMyAdmin
1. Ouvrez le panneau de contrôle XAMPP et démarrez les services **Apache** et **MySQL**.
2. Rendez-vous sur `http://localhost/phpmyadmin/`.
3. Cliquez sur l'onglet **Importer**.
4. Importez successivement dans cet ordre :
   1. `database/schema.sql` (Crée la base de données `zouza_shop` et l'ensemble des 5 tables : `users`, `categories`, `products`, `orders`, `order_items`).
   2. `database/seed.sql` (Optionnel : Insère des catégories de test, des délices Zouza et des comptes de démonstration).

### Méthode B : Ligne de Commande (CLI XAMPP)
```powershell
# 1. Création de la structure
C:\xampp\mysql\bin\mysql.exe -u root < "database\schema.sql"

# 2. Injection des données de test
C:\xampp\mysql\bin\mysql.exe -u root < "database\seed.sql"
```

### Comptes de Test Inclus dans le Seed
| Rôle | Adresse Email | Mot de passe | Accès |
| :--- | :--- | :--- | :--- |
| **Administrateur** | `admin@zouza.local` | `admin123` | Back Office (`/admin`) + Front Office |
| **Client Test** | `client@zouza.local` | `client123` | Front Office (`/profile`, `/orders`) |

*(Les mots de passe sont hashés en base via l'algorithme sécurisé `PASSWORD_DEFAULT` / Bcrypt).*

---

## 5. Architecture Logicielle & Structure MVC

Le projet applique une séparation stricte des responsabilités selon le patron MVC :
```
zouza-shop/
├── .htaccess                   # Réécriture d'URL vers Front Controller et règles de sécurité
├── .gitignore                  # Exclusion des secrets, uploads, logs et scripts temporaires
├── index.php                   # Front Controller unique : session, sécurité, routage
├── robots.txt                  # Directives pour les robots d'indexation
│
├── config/                     # Configuration générale et connexion PDO
│   ├── config.php              # Constantes globales, détection d'URL, gestion d'erreurs
│   ├── config.example.php      # Modèle pour configuration locale
│   └── database.php            # Singleton PDO avec gestion robuste des exceptions
│
├── core/                       # Socle MVC léger
│   ├── Controller.php          # Contrôleur de base (render, redirection, helpers JSON)
│   ├── Model.php               # Modèle de base avec instance PDO centralisée
│   ├── Router.php              # Routeur HTTP (GET, POST, paramètres de requête)
│   ├── Session.php             # Gestion des sessions durcies et jetons CSRF
│   └── ErrorHandler.php        # Gestionnaire d'erreurs et pages d'exception conviviales
│
├── controllers/                # Contrôleurs HTTP applicatifs
│   ├── HomeController.php      # Page d'accueil vitrine
│   ├── ProductController.php   # Catalogue, filtres, recherche, fiches produits
│   ├── CartController.php      # Gestion du panier en session
│   ├── OrderController.php     # Checkout invité/connecté et suivi de commande
│   ├── AuthController.php      # Connexion, déconnexion sécurisée
│   ├── ProfileController.php   # Profil client et mise à jour des coordonnées
│   ├── SitemapController.php   # Génération dynamique du sitemap.xml et robots.txt
│   └── admin/                  # Contrôleurs de l'espace d'administration
│       ├── AdminDashboardController.php
│       ├── AdminCategoryController.php
│       ├── AdminProductController.php
│       └── AdminOrderController.php
│
├── models/                     # Modèles d'accès aux données (Requêtes préparées PDO)
│   ├── User.php
│   ├── Category.php
│   ├── Product.php
│   ├── Cart.php
│   └── Order.php
│
├── views/                      # Vues HTML / PHP
│   ├── layouts/                # Gabarits maîtres (front.php, admin.php)
│   ├── frontoffice/            # Vues de la boutique publique
│   ├── backoffice/             # Vues de gestion d'administration
│   ├── auth/                   # Formulaire de connexion
│   └── errors/                 # Pages d'erreur 404 et 500 personnalisées
│
├── database/                   # Scripts de persistance
│   ├── schema.sql              # Définition DDL des tables et contraintes
│   ├── seed.sql                # Jeu d'essai initial
│   └── README.md               # Documentation spécifique au schéma de base de données
│
├── assets/                     # Fichiers statiques (CSS, JS, visuels fixes)
└── uploads/                    # Médias uploadés (photos de produits - exclus de Git)
```

---

## 6. Référencement Naturel (SEO) & Performances

- **Sitemap XML Dynamique** : Accessible à l'adresse `/sitemap.xml`. Construit automatiquement en temps réel à partir des produits disponibles et catégories actives, conforme au protocole Sitemaps 0.9.
- **Directives Robots** : Fichier `/robots.txt` autorisant le catalogue public tout en protégeant les espaces privés (`/admin`, `/cart`, `/checkout`, `/login`).
- **Protection des Pages Privées** : En-têtes et balises `<meta name="robots" content="noindex, nofollow">` sur toutes les vues d'administration et la page d'authentification.
- **Balises Canoniques & Open Graph** : Présentes sur toutes les pages publiques pour optimiser l'indexation et l'affichage sur les réseaux sociaux.
- **Optimisation des Images** : Attribut `loading="lazy"` activé sur les grilles de produits pour minimiser le temps de chargement initial.
- **Précision Monétaire** : Stockage et calculs financiers rigoureux en `DECIMAL(10, 3)` pour les millimes tunisiens (ex: `18.500 DT`).

---

## 7. Sécurité Applicative

- **Injections SQL** : 100% des requêtes vers MySQL utilisent des requêtes préparées PDO avec typage des paramètres (`bindValue`).
- **Failles XSS** : Échappement systématique des données utilisateur via la fonction globale `e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).
- **Failles CSRF** : Protection par jeton cryptographique aléatoire (`bin2hex(random_bytes(32))`) injecté dans tous les formulaires critiques (`POST`).
- **Authentification** : Mots de passe hashés avec `password_hash($pass, PASSWORD_DEFAULT)` et vérifiés avec `password_verify()`. Régénération de session (`session_regenerate_id(true)`) lors de la connexion pour contrer la fixation de session.
- **En-têtes HTTP de Sécurité** : `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`.
- **Défense Apache** : `.htaccess` bloque la navigation dans les répertoires (`Options -Indexes`) et refuse l'accès direct aux fichiers sensibles (`.env`, `.sql`, `.log`, `.git`).

---

## 8. Guide de Déploiement en Production

### 1. Variables d'Environnement & Configuration
Sur le serveur de production, créez le fichier `config/config.local.php` :
```php
<?php
define('APP_ENV', 'production'); // Désactive l'affichage des erreurs à l'écran
define('BASE_URL', 'https://www.votredomaine.com');

// Identifiants MySQL dédiés (non root) :
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'nom_de_votre_base');
define('DB_USER', 'utilisateur_dedie');
define('DB_PASS', 'mot_de_passe_ultra_robuste');
```

### 2. Permissions de Fichiers (Linux)
```bash
# Propriétaire serveur web (ex: www-data)
chown -R www-data:www-data /var/www/zouza-shop

# Permissions strictes de lecture seule sur le code
find /var/www/zouza-shop -type d -exec chmod 755 {} \;
find /var/www/zouza-shop -type f -exec chmod 644 {} \;

# Autoriser l'écriture uniquement dans le dossier d'uploads
chmod -R 775 /var/www/zouza-shop/uploads
```

### 3. Forçage HTTPS (Apache)
Assurez-vous qu'un certificat SSL/TLS valide (ex: Let's Encrypt) est installé. Vous pouvez décommenter ou ajouter dans votre VirtualHost :
```apache
<VirtualHost *:80>
    ServerName www.votredomaine.com
    Redirect permanent / https://www.votredomaine.com/
</VirtualHost>
```

### 4. Mots de Passe Initiaux
Modifiez immédiatement le mot de passe du compte administrateur initial (`admin@zouza.local`) après la première connexion sur le serveur de production.

---

## 9. Licence & Auteur

Projet d'apprentissage et portfolio réalisé avec passion pour valoriser la pâtisserie artisanale tunisienne.
Tous droits réservés © <?= date('Y') ?> Zouza Shop.
