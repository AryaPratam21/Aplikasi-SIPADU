<?php
// Load all required configurations
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/maintenance.php';

// Create required directories if not exist
$directories = [
    LOG_PATH,
    BACKUP_PATH,
    UPLOAD_PATH . '/foto_penduduk/ktp',
    UPLOAD_PATH . '/foto_penduduk/kk',
    UPLOAD_PATH . '/foto_penduduk/profil',
    UPLOAD_PATH . '/dokumen/akta_kelahiran',
    UPLOAD_PATH . '/dokumen/akta_kematian',
    UPLOAD_PATH . '/dokumen/akta_nikah',
    UPLOAD_PATH . '/dokumen/akta_cerai'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Clean old log files
$logs = glob(LOG_PATH . '/*.log');
if (count($logs) > MAX_LOG_FILES) {
    array_map('unlink', array_slice($logs, 0, count($logs) - MAX_LOG_FILES));
}

// Clean old backup files
$backups = glob(BACKUP_PATH . '/*.sql');
if (count($backups) > MAX_BACKUP_FILES) {
    array_map('unlink', array_slice($backups, 0, count($backups) - MAX_BACKUP_FILES));
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Register error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error = date('Y-m-d H:i:s') . " - Error [$errno] $errstr in $errfile on line $errline\n";
    error_log($error, 3, LOG_PATH . '/error.log');
    
    if (ini_get('display_errors')) {
        echo "An error occurred. Please try again later.";
    }
    
    return true;
}); 