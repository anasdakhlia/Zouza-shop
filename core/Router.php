<?php
/**
 * ZOUZA SHOP - Routeur MVC
 *
 * Mappe les requêtes HTTP (méthode + URL) vers le contrôleur et l'action appropriés.
 */

class Router
{
    private array $routes = [];

    /**
     * Enregistre une route accessible en méthode GET
     */
    public function get(string $path, array|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Enregistre une route accessible en méthode POST
     */
    public function post(string $path, array|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Ajoute une route à la table interne
     */
    private function addRoute(string $method, string $path, array|callable $handler): void
    {
        $normalizedPath = '/' . trim($path, '/');
        if ($normalizedPath !== '/') {
            $normalizedPath = rtrim($normalizedPath, '/');
        }

        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $normalizedPath,
            'handler' => $handler,
        ];
    }

    /**
     * Analyse la requête courante et exécute le gestionnaire associé
     */
    public function dispatch(string $requestMethod, string $requestUri): void
    {
        $parsedUrl = parse_url($requestUri, PHP_URL_PATH) ?? '/';

        // Supprime le sous-dossier éventuel du projet si exécuté sous XAMPP (ex: /zouza-shop)
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($parsedUrl, $scriptDir)) {
            $parsedUrl = substr($parsedUrl, strlen($scriptDir));
        }

        $normalizedPath = '/' . trim($parsedUrl, '/');
        if ($normalizedPath !== '/') {
            $normalizedPath = rtrim($normalizedPath, '/');
        }

        $method = strtoupper($requestMethod);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $normalizedPath) {
                $this->executeHandler($route['handler']);
                return;
            }
        }

        // Aucune route correspondante : déclenchement du 404
        ErrorHandler::renderNotFound("La page demandée '{$normalizedPath}' n'existe pas.");
    }

    /**
     * Exécute une closure ou une méthode de contrôleur
     */
    private function executeHandler(array|callable $handler): void
    {
        if (is_callable($handler)) {
            $handler();
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $actionMethod] = $handler;

            if (!class_exists($controllerClass)) {
                throw new RuntimeException("Contrôleur '{$controllerClass}' introuvable.");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $actionMethod)) {
                throw new RuntimeException("Action '{$actionMethod}' introuvable dans le contrôleur '{$controllerClass}'.");
            }

            $controller->$actionMethod();
            return;
        }

        throw new RuntimeException("Format de gestionnaire de route invalide.");
    }
}
