<?php
// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Direct access not permitted');
}

// Error reporting untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Session configuration
    $session_options = [
        'cookie_httponly' => 1,
        'use_only_cookies' => 1,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'gc_maxlifetime' => 3600, // 1 jam
        'cookie_lifetime' => 0,    // Sampai browser ditutup
        'use_strict_mode' => 1,    // Untuk keamanan tambahan
        'cookie_samesite' => 'Lax' // Proteksi CSRF
    ];

    // Set session options sebelum memulai session
    foreach ($session_options as $key => $value) {
        ini_set("session.$key", $value);
    }
    
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Load constants
require_once __DIR__ . '/constants.php';
?>
