<?php
/**
 * ZOUZA SHOP - Gestionnaire Centralisé des Erreurs et Exceptions
 *
 * Isole la gestion des erreurs PHP et des exceptions non interceptées.
 * Différencie le comportement de développement (détails et traces)
 * et de production (sécurité, masquage des détails internes et journalisation).
 */

class ErrorHandler
{
    /**
     * Enregistre les gestionnaires PHP natifs
     */
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    /**
     * Transforme les erreurs PHP standard en ErrorException
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        // Si l'erreur a été masquée par l'opérateur @, on ne fait rien
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Traite les exceptions non interceptées
     */
    public static function handleException(Throwable $e): void
    {
        // Journalisation sécurisée de l'erreur dans les logs du serveur
        error_log(sprintf(
            "[%s] Uncaught exception '%s': %s in %s:%d\nTrace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));

        // Détection du code HTTP
        $code = $e->getCode();
        $statusCode = ($code >= 400 && $code <= 599) ? (int)$code : 500;

        if ($statusCode === 404) {
            self::renderNotFound($e->getMessage());
            return;
        }

        if ($statusCode === 403) {
            self::renderForbidden($e->getMessage());
            return;
        }

        self::renderServerError($e);
    }

    /**
     * Affiche une réponse 404 contrôlée
     */
    public static function renderNotFound(string $message = 'Page introuvable'): void
    {
        http_response_code(404);
        $pageTitle = '404 - Page Non Trouvée';
        $viewPath = __DIR__ . '/../views/errors/404.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>404</title></head><body>';
            echo '<h1>404 - Page Non Trouvée</h1>';
            echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<a href="' . BASE_URL . '/">Retour à l\'accueil</a>';
            echo '</body></html>';
        }
        exit;
    }

    /**
     * Affiche une réponse 403 contrôlée (Accès interdit)
     */
    public static function renderForbidden(string $message = 'Accès non autorisé'): void
    {
        http_response_code(403);
        $pageTitle = '403 - Accès Interdit';
        $viewPath = __DIR__ . '/../views/errors/403.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>403</title></head><body>';
            echo '<h1>403 - Accès Interdit</h1>';
            echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<a href="' . BASE_URL . '/">Retour à l\'accueil</a>';
            echo '</body></html>';
        }
        exit;
    }

    /**
     * Affiche une réponse 500 contrôlée
     */
    public static function renderServerError(Throwable $e): void
    {
        http_response_code(500);
        $pageTitle = '500 - Erreur Interne du Serveur';
        $isDev = (defined('APP_ENV') && APP_ENV === 'development');
        $viewPath = __DIR__ . '/../views/errors/500.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>500</title></head><body>';
            echo '<h1>500 - Erreur Interne du Serveur</h1>';
            if ($isDev) {
                echo '<p><strong>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</strong></p>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
            } else {
                echo '<p>Une anomalie technique est survenue. Veuillez réessayer ultérieurement.</p>';
            }
            echo '<a href="' . BASE_URL . '/">Retour à l\'accueil</a>';
            echo '</body></html>';
        }
        exit;
    }
}
