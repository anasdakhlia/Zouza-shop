<?php
/**
 * ZOUZA SHOP - Contrôleur de Base
 *
 * Fournit les fonctionnalités communes réutilisables par les contrôleurs enfants.
 */

abstract class Controller
{
    /**
     * Rend une vue avec des données et un layout optionnel
     *
     * @param string $view Chemin relatif dans le dossier views/ (ex: 'frontoffice/home')
     * @param array $data Données transmises à la vue
     * @param string|null $layout Nom du layout dans views/layouts/ (ex: 'front', 'admin' ou null)
     */
    protected function render(string $view, array $data = [], ?string $layout = 'front'): void
    {
        // Extraction sécurisée des variables pour la vue
        extract($data, EXTR_SKIP);

        // Nettoyage du chemin pour interdire la traversée de répertoire
        $sanitizedView = str_replace(['../', '..\\'], '', $view);
        $viewPath = __DIR__ . '/../views/' . trim($sanitizedView, '/') . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException("Fichier de vue introuvable : views/{$sanitizedView}.php");
        }

        // Capture du rendu de la vue dans un tampon
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Si un layout est demandé, l'inclure en lui injectant $content
        if ($layout !== null) {
            $sanitizedLayout = str_replace(['../', '..\\'], '', $layout);
            $layoutPath = __DIR__ . '/../views/layouts/' . $sanitizedLayout . '.php';

            if (!file_exists($layoutPath)) {
                throw new RuntimeException("Fichier de layout introuvable : views/layouts/{$sanitizedLayout}.php");
            }

            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Redirige vers une URL relative à BASE_URL ou absolue
     *
     * @param string $path
     */
    protected function redirect(string $path): void
    {
        if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
            $path = BASE_URL . '/' . ltrim($path, '/');
        }

        header("Location: {$path}");
        exit;
    }

    /**
     * Retourne une réponse au format JSON
     *
     * @param mixed $data
     * @param int $statusCode
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
