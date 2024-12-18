<?php
// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

// Definisikan DEBUG_MODE jika belum didefinisikan
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}

// Error handler function
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Error message untuk log
    $error_message = date('Y-m-d H:i:s') . " - ";
    
    // Get error type
    switch ($errno) {
        case E_ERROR:
            $error_message .= "Error";
            break;
        case E_WARNING:
            $error_message .= "Warning";
            break;
        case E_PARSE:
            $error_message .= "Parse Error";
            break;
        case E_NOTICE:
            $error_message .= "Notice";
            break;
        case E_CORE_ERROR:
            $error_message .= "Core Error";
            break;
        case E_CORE_WARNING:
            $error_message .= "Core Warning";
            break;
        case E_COMPILE_ERROR:
            $error_message .= "Compile Error";
            break;
        case E_COMPILE_WARNING:
            $error_message .= "Compile Warning";
            break;
        case E_USER_ERROR:
            $error_message .= "User Error";
            break;
        case E_USER_WARNING:
            $error_message .= "User Warning";
            break;
        case E_USER_NOTICE:
            $error_message .= "User Notice";
            break;
        default:
            $error_message .= "Unknown Error";
            break;
    }
    
    // Format pesan error
    $error_message .= ": $errstr in $errfile on line $errline\n";
    
    // Log error ke file
    error_log($error_message, 3, LOG_PATH . '/error.log');
    
    // Jika error fatal, redirect ke halaman 500
    if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        if (!headers_sent()) {
            header("Location: " . BASE_URL . "/errors/500.php");
            exit;
        }
    }
    
    // Jika error kritis, catat ke sistem notifikasi
    if ($errno == E_ERROR || $errno == E_USER_ERROR) {
        try {
            if (class_exists('Database')) {
                $db = (new Database())->getConnection();
                if (class_exists('NotificationHandler')) {
                    $notification = new NotificationHandler($db);
                    $notification->addNotification(
                        'System Error',
                        $error_message,
                        'critical'
                    );
                }
            }
        } catch (Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
        }
    }
    
    return true;
}

// Exception handler function
function customExceptionHandler($exception) {
    // Log exception
    $error_message = date('Y-m-d H:i:s') . " - Exception: " . 
                    $exception->getMessage() . " in " . 
                    $exception->getFile() . " on line " . 
                    $exception->getLine() . "\n" .
                    "Stack trace:\n" . $exception->getTraceAsString() . "\n";
    
    error_log($error_message, 3, LOG_PATH . '/error.log');
    
    if (DEBUG_MODE === false) {
        if (!headers_sent()) {
            header("Location: " . BASE_URL . "/errors/500.php");
            exit;
        }
    } else {
        echo "<h1>Error</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    }
}

// Shutdown handler function
function customShutdownHandler() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Log fatal error
        $error_message = date('Y-m-d H:i:s') . " - Fatal Error: " . 
                        $error['message'] . " in " . 
                        $error['file'] . " on line " . 
                        $error['line'] . "\n";
        
        error_log($error_message, 3, LOG_PATH . '/error.log');
        
        if (!headers_sent()) {
            header("Location: " . BASE_URL . "/errors/500.php");
        }
    }
}

// Set error handlers
set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");
register_shutdown_function("customShutdownHandler");

// Error reporting settings
if (DEBUG_MODE === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Pastikan folder log ada dan bisa ditulis
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Rotasi file log jika terlalu besar
$log_file = LOG_PATH . '/error.log';
if (file_exists($log_file) && filesize($log_file) > 5 * 1024 * 1024) { // 5MB
    $backup_file = LOG_PATH . '/error_' . date('Y-m-d_H-i-s') . '.log';
    rename($log_file, $backup_file);
    
    // Hapus log backup yang lebih dari 30 hari
    $old_logs = glob(LOG_PATH . '/error_*.log');
    foreach ($old_logs as $old_log) {
        if (filemtime($old_log) < strtotime('-30 days')) {
            unlink($old_log);
        }
    }
}

try {
    // kode yang mungkin error
} catch (Exception $e) {
    // error akan ditangani oleh exception handler
} 