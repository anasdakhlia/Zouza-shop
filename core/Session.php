<?php
/**
 * ZOUZA SHOP - Gestionnaire de Session & Sécurité CSRF
 *
 * Gère le cycle de vie des sessions PHP, les cookies sécurisés,
 * la régénération d'identifiant de session et la protection CSRF.
 */

class Session
{
    /**
     * Initialise la session avec des paramètres de cookies stricts
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                     || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        }
    }

    /**
     * Génère ou récupère le jeton CSRF courant
     */
    public static function getCsrfToken(): string
    {
        self::start();

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Valide le jeton CSRF soumis de façon sécurisée (résistant au timing attack)
     */
    public static function verifyCsrfToken(?string $token): bool
    {
        self::start();

        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Régénère un nouveau jeton CSRF
     */
    public static function regenerateCsrfToken(): string
    {
        self::start();
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf_token'];
    }

    /**
     * Connecte l'utilisateur en régénérant l'ID de session (anti session-fixation)
     */
    public static function login(array $user): void
    {
        self::start();
        session_regenerate_id(true);

        // Enregistre uniquement les données nécessaires (sans hash de mot de passe)
        $_SESSION['user'] = [
            'user_id' => (int)$user['user_id'],
            'nom'     => $user['nom'],
            'prenom'  => $user['prenom'],
            'email'   => $user['email'],
            'role'    => $user['role'],
        ];

        // Nouveau jeton CSRF à la connexion
        self::regenerateCsrfToken();
    }

    /**
     * Déconnecte l'utilisateur et détruit complètement la session
     */
    public static function logout(): void
    {
        self::start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Retourne les données de l'utilisateur connecté ou null
     */
    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Vérifie si un utilisateur est authentifié
     */
    public static function isLoggedIn(): bool
    {
        return self::user() !== null;
    }

    /**
     * Vérifie si l'utilisateur connecté possède le rôle administrateur
     */
    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && isset($user['role']) && $user['role'] === 'admin';
    }

    /**
     * Vérifie si l'utilisateur possède un rôle spécifique
     */
    public static function hasRole(string $role): bool
    {
        $user = self::user();
        return $user !== null && isset($user['role']) && $user['role'] === $role;
    }

    /**
     * Définit un message flash pour la prochaine requête
     */
    public static function setFlash(string $key, string $message): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $message;
    }

    /**
     * Récupère et supprime un message flash
     */
    public static function getFlash(string $key): ?string
    {
        self::start();
        if (isset($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }
        return null;
    }

    /**
     * Vérifie si un message flash existe
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return !empty($_SESSION['_flash'][$key]);
    }
}
