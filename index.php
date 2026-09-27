<?php
/**
 * ZOUZA SHOP - Front Controller & Point d'Entrée Unique
 *
 * Centralise toutes les requêtes HTTP, initialise la configuration,
 * enregistre le gestionnaire d'erreurs et dispatche vers le routeur.
 */

// 1. Initialisation de la configuration générale
require_once __DIR__ . '/config/config.php';

// 2. Enregistrement du gestionnaire d'erreurs centralisé
require_once __DIR__ . '/core/ErrorHandler.php';
ErrorHandler::register();

// 3. Chargement du socle MVC
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';

// 4. Chargement des contrôleurs
require_once __DIR__ . '/controllers/HomeController.php';

// 5. Définition des routes de l'application
$router = new Router();

// Route d'accueil
$router->get('/', [HomeController::class, 'index']);

// 6. Exécution du routage
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($requestMethod, $requestUri);
