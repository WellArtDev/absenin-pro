<?php
/**
 * Absenin - Main Configuration
 */

// App
define('APP_NAME', 'Absenin');
define('APP_URL', getenv('APP_URL') ?: 'http://api.absenin.test');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') ?: true);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Paths
define('ROOT_DIR', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_DIR . '/public/uploads');
define('LOG_DIR', ROOT_DIR . '/logs');

// JWT
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'change-this-to-a-secure-random-string');
define('JWT_ALGORITHM', 'HS256');
define('JWT_ACCESS_EXPIRY', 86400);   // 24 hours
define('JWT_REFRESH_EXPIRY', 2592000); // 30 days

// Rate Limiting
define('RATE_LIMIT_MAX', 60);    // requests
define('RATE_LIMIT_WINDOW', 60); // seconds

// Upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('SELFIE_MAX_WIDTH', 800);

// FCM
define('FCM_SERVER_KEY', getenv('FCM_SERVER_KEY') ?: '');

// CORS
define('CORS_ORIGINS', getenv('CORS_ORIGINS') ?: 'http://hub.absenin.test');

require_once ROOT_DIR . '/config/constants.php';
