<?php
/**
 * ZOUZA SHOP - Configuration Generale
 *
 * Ce fichier definit les constantes globales de l'application et charge
 * la configuration locale specifique a l'environnement si elle existe.
 */

// 1. Definition de l'environnement ('development' ou 'production')
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

// 2. Parametres generaux du site
define('APP_NAME', 'Zouza Shop');
define('APP_TAGLINE', 'Patisserie Artisanale Tunisienne');
define('APP_CURRENCY', 'TND');
define('APP_CURRENCY_DECIMALS', 3);
define('APP_CURRENCY_SYMBOL', 'DT');

// 3. Detection dynamique de l'URL de base (compatible XAMPP sous-dossier ou VirtualHost)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protocol . $host . $scriptDir, '/');

if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

// 4. Parametres par defaut de la base de donnees (XAMPP standard)
$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbName = 'zouza_shop';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

// 5. Chargement de la configuration locale personnalisee si elle existe
// (config.local.php est exclu de Git par .gitignore pour proteger les identifiants)
$localConfigFile = __DIR__ . '/config.local.php';
if (file_exists($localConfigFile)) {
    require_once $localConfigFile;
}

// 6. Definition des constantes de base de donnees si non definies par config.local.php
if (!defined('DB_HOST')) define('DB_HOST', $dbHost);
if (!defined('DB_PORT')) define('DB_PORT', $dbPort);
if (!defined('DB_NAME')) define('DB_NAME', $dbName);
if (!defined('DB_USER')) define('DB_USER', $dbUser);
if (!defined('DB_PASS')) define('DB_PASS', $dbPass);
if (!defined('DB_CHARSET')) define('DB_CHARSET', $dbCharset);

// 7. Gestion de l'affichage des erreurs selon l'environnement
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}
