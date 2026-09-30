<?php
/**
 * ZOUZA SHOP - Contrôleur de Base
 *
 * Fournit les fonctionnalités communes réutilisables par les contrôleurs enfants :
 * rendu de vues, redirections, réponses JSON, authentification, rôles et protection CSRF.
 */

require_once __DIR__ . '/Session.php';

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
        // Données globales automatiquement disponibles dans les vues
        $defaultData = [
            'currentUser'  => Session::user(),
            'csrfToken'    => Session::getCsrfToken(),
            'flashSuccess' => Session::getFlash('success'),
            'flashError'   => Session::getFlash('error'),
            'flashInfo'    => Session::getFlash('info'),
        ];

        // Fusion des données par défaut et des données passées
        $viewData = array_merge($defaultData, $data);
        extract($viewData, EXTR_SKIP);

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
     * Exige qu'un utilisateur soit authentifié pour continuer
     */
    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Veuillez vous connecter pour accéder à cette page.');
            $this->redirect('/login');
        }
    }

    /**
     * Exige un rôle spécifique pour continuer (sinon 403 Forbidden)
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        if (!Session::hasRole($role)) {
            ErrorHandler::renderForbidden("Vous n'avez pas les droits d'accès requis pour cette section.");
        }
    }

    /**
     * Exige le rôle administrateur (sinon 403 Forbidden)
     */
    protected function requireAdmin(): void
    {
        $this->requireRole('admin');
    }

    /**
     * Valide le jeton CSRF pour les requêtes modifiant l'état (POST)
     */
    protected function validateCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? null;

        if (!Session::verifyCsrfToken($token)) {
            ErrorHandler::renderForbidden('Requête refusée : jeton CSRF manquant ou invalide.');
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
