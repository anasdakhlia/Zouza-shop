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

// 5. Chargement des contrôleurs
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/admin/AdminDashboardController.php';
require_once __DIR__ . '/controllers/admin/AdminCategoryController.php';
require_once __DIR__ . '/controllers/admin/AdminProductController.php';

// 6. Définition des routes de l'application
$router = new Router();

// Routes publiques
$router->get('/', [HomeController::class, 'index']);
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/detail', [ProductController::class, 'detail']);

// Routes d'authentification
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// Routes d'administration protégées
$router->get('/admin', [AdminDashboardController::class, 'index']);

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

// 7. Exécution du routage
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($requestMethod, $requestUri);
