<?php
// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Direct access not permitted');
}

// Hanya definisikan konstanta jika belum ada
if (!defined('DB_HOST'))     define('DB_HOST', 'localhost');
if (!defined('DB_NAME'))     define('DB_NAME', 'sipadu');
if (!defined('DB_USER'))     define('DB_USER', 'root');
if (!defined('DB_PASS'))     define('DB_PASS', '');
if (!defined('DB_CHARSET'))  define('DB_CHARSET', 'utf8mb4');

// Application settings
if (!defined('APP_NAME'))    define('APP_NAME', 'SIPADU');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('BASE_PATH'))   define('BASE_PATH', realpath(__DIR__ . '/..'));
if (!defined('BASE_URL'))    define('BASE_URL', 'http://localhost/sipadu');

// Security settings
if (!defined('HASH_COST'))   define('HASH_COST', 10);