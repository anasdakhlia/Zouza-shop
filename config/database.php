<?php
/**
 * ZOUZA SHOP - Connexion Base de Donnees (PDO)
 *
 * Fournit une connexion PDO unique (Singleton simple) configuree avec :
 * - Gestion stricte des erreurs par exceptions (ERRMODE_EXCEPTION)
 * - Fetch mode par defaut en tableau associatif (FETCH_ASSOC)
 * - Requetes preparees natives desactivees d'emulation (EMULATE_PREPARES => false)
 * - Encodage UTF-8 complet (utf8mb4)
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static ?PDO $instance = null;

    /**
     * Empeche l'instanciation directe
     */
    private function __construct() {}

    /**
     * Empeche le clonage de l'instance
     */
    private function __clone() {}

    /**
     * Retourne l'instance unique de connexion PDO
     *
     * @return PDO
     * @throws PDOException En cas d'echec de connexion
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // En production, masquer les details techniques sensibles
                if (defined('APP_ENV') && APP_ENV === 'production') {
                    error_log('Erreur de connexion BDD : ' . $e->getMessage());
                    throw new RuntimeException('Impossible de se connecter a la base de donnees. Veuillez reessayer ulterieurement.');
                }
                // En developpement, remonter l'exception avec les details utiles
                throw $e;
            }
        }

        return self::$instance;
    }
}

/**
 * Fonction d'aide globale pour obtenir facilement la connexion PDO
 *
 * @return PDO
 */
function getDbConnection(): PDO
{
    return Database::getConnection();
}
