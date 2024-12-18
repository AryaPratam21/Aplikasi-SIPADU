<?php
// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Direct access not permitted');
}

// Maintenance mode settings
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.');
define('MAINTENANCE_ALLOWED_IPS', [
    '127.0.0.1',
    // Tambahkan IP yang diizinkan akses saat maintenance
]);

// Check maintenance mode
if (MAINTENANCE_MODE && !in_array($_SERVER['REMOTE_ADDR'], MAINTENANCE_ALLOWED_IPS)) {
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 3600'); // 1 hour
    
    // Display maintenance page
    include BASE_PATH . '/includes/maintenance.php';
    exit;
} 