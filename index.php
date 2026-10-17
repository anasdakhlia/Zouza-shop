<?php
/**
 * ZOUZA SHOP - Front Controller & Point d'Entrée Unique
 *
 * Centralise toutes les requêtes HTTP, initialise la configuration,
 * enregistre le gestionnaire d'erreurs et dispatche vers le routeur.
 */

// 1. Initialisation de la configuration générale
require_once __DIR__ . '/config/config.php';

// 2. Initialisation de la gestion des sessions sécurisées
require_once __DIR__ . '/core/Session.php';
Session::start();

// 3. Enregistrement du gestionnaire d'erreurs centralisé
require_once __DIR__ . '/core/ErrorHandler.php';
ErrorHandler::register();

// 4. Chargement du socle MVC
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';

// 5. Chargement des modèles transversaux et contrôleurs
require_once __DIR__ . '/models/Cart.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/admin/AdminDashboardController.php';
require_once __DIR__ . '/controllers/admin/AdminCategoryController.php';
require_once __DIR__ . '/controllers/admin/AdminProductController.php';
require_once __DIR__ . '/controllers/admin/AdminOrderController.php';

// 6. Définition des routes de l'application
$router = new Router();

// Routes publiques
$router->get('/', [HomeController::class, 'index']);
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/detail', [ProductController::class, 'detail']);

// Panier d'achat (accessible à tous : invités et connectés)
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->post('/cart/clear', [CartController::class, 'clear']);

// Commande client (Checkout & Suivi)
$router->get('/checkout', [OrderController::class, 'checkout']);
$router->post('/checkout', [OrderController::class, 'processCheckout']);
$router->get('/order/confirmation', [OrderController::class, 'confirmation']);
$router->get('/orders', [OrderController::class, 'myOrders']);
$router->get('/orders/detail', [OrderController::class, 'detail']);

// Espace client personnel (Profil)
$router->get('/profile', [ProfileController::class, 'show']);
$router->post('/profile', [ProfileController::class, 'update']);
$router->post('/profile/password', [ProfileController::class, 'updatePassword']);

// Routes d'authentification
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// Routes d'administration protégées
$router->get('/admin', [AdminDashboardController::class, 'index']);
$router->get('/admin/dashboard', [AdminDashboardController::class, 'index']);

// Administration des catégories
$router->get('/admin/categories', [AdminCategoryController::class, 'index']);
$router->get('/admin/categories/create', [AdminCategoryController::class, 'create']);
$router->post('/admin/categories/create', [AdminCategoryController::class, 'store']);
$router->get('/admin/categories/edit', [AdminCategoryController::class, 'edit']);
$router->post('/admin/categories/edit', [AdminCategoryController::class, 'update']);
$router->post('/admin/categories/delete', [AdminCategoryController::class, 'delete']);

// Administration des produits
$router->get('/admin/products', [AdminProductController::class, 'index']);
$router->get('/admin/products/create', [AdminProductController::class, 'create']);
$router->post('/admin/products/create', [AdminProductController::class, 'store']);
$router->get('/admin/products/edit', [AdminProductController::class, 'edit']);
$router->post('/admin/products/edit', [AdminProductController::class, 'update']);
$router->post('/admin/products/delete', [AdminProductController::class, 'delete']);

// Administration des commandes
$router->get('/admin/orders', [AdminOrderController::class, 'index']);
$router->get('/admin/orders/detail', [AdminOrderController::class, 'detail']);
$router->post('/admin/orders/status', [AdminOrderController::class, 'updateStatus']);

// 7. Exécution du routage
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($requestMethod, $requestUri);
