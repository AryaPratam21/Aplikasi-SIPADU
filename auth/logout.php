<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    try {
        $db = (new Database())->getConnection();
        
        // Log activity
        logAktivitas($db, $_SESSION['user_id'], 'HAPUS', 'Logout dari sistem');
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear cookies
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// Redirect to login page
header('Location: ' . BASE_URL . '/auth/login.php');
exit();