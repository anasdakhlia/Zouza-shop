<?php
/**
 * ZOUZA SHOP - Configuration Generale
 *
 * Ce fichier definit les constantes globales de l'application et charge
 * la configuration locale specifique a l'environnement si elle existe.
 */

// 1. Chargement de la configuration locale personnalisee si elle existe
// (config.local.php est exclu de Git par .gitignore pour proteger les identifiants)
$localConfigFile = __DIR__ . '/config.local.php';
if (file_exists($localConfigFile)) {
    require_once $localConfigFile;
}

// 2. Definition de l'environnement par defaut ('development' ou 'production')
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

// 3. Parametres generaux du site
if (!defined('APP_NAME'))              define('APP_NAME', 'Zouza Shop');
if (!defined('APP_TAGLINE'))           define('APP_TAGLINE', 'Patisserie Artisanale Tunisienne');
if (!defined('APP_CURRENCY'))          define('APP_CURRENCY', 'TND');
if (!defined('APP_CURRENCY_DECIMALS')) define('APP_CURRENCY_DECIMALS', 3);
if (!defined('APP_CURRENCY_SYMBOL'))   define('APP_CURRENCY_SYMBOL', 'DT');

// 4. Detection dynamique de l'URL de base (compatible XAMPP sous-dossier ou VirtualHost)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $baseUrl = rtrim($protocol . $host . $scriptDir, '/');
    define('BASE_URL', $baseUrl);
}

// 5. Parametres par defaut de la base de donnees (XAMPP standard)
if (!defined('DB_HOST'))    define('DB_HOST', '127.0.0.1');
if (!defined('DB_PORT'))    define('DB_PORT', '3306');
if (!defined('DB_NAME'))    define('DB_NAME', 'zouza_shop');
if (!defined('DB_USER'))    define('DB_USER', 'root');
if (!defined('DB_PASS'))    define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// 6. Gestion de l'affichage des erreurs selon l'environnement
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}
