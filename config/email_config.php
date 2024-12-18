<?php
// Email settings
if (!defined('SMTP_FROM')) {
    define('SMTP_HOST', 'smtp.gmail.com');
    define('SMTP_PORT', 587);
    define('SMTP_USER', 'your@gmail.com');
    define('SMTP_PASS', 'your-password');
    define('SMTP_FROM', 'noreply@yourdomain.com');
    define('SMTP_FROM_NAME', APP_NAME);
} 