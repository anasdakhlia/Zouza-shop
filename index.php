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
require_once __DIR__ . '/controllers/admin/AdminDashboardController.php';

// 6. Définition des routes de l'application
$router = new Router();

// Routes publiques
$router->get('/', [HomeController::class, 'index']);

// Routes d'authentification
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// Route d'administration protégée
$router->get('/admin', [AdminDashboardController::class, 'index']);

// 7. Exécution du routage
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($requestMethod, $requestUri);
