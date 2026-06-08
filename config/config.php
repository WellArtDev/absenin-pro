<?php

define('ROOT_DIR', dirname(__DIR__));
require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/config/env.php';

date_default_timezone_set('Asia/Jakarta');

define('APP_NAME', 'Absenin');
define('APP_URL', getenv('APP_URL') ?: 'http://api.absenin.test');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN));

define('UPLOAD_DIR', ROOT_DIR . '/public/uploads');
define('LOG_DIR', ROOT_DIR . '/logs');

define('JWT_SECRET', getenv('JWT_SECRET'));
define('JWT_ALGORITHM', 'HS256');
define('JWT_ACCESS_EXPIRY', 86400);
define('JWT_REFRESH_EXPIRY', 2592000);

if (empty(JWT_SECRET)) {
    die('JWT_SECRET is not set. Check your .env file.');
}

define('RATE_LIMIT_MAX', 60);
define('RATE_LIMIT_WINDOW', 60);

define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('SELFIE_MAX_WIDTH', 800);

define('FCM_SERVER_KEY', getenv('FCM_SERVER_KEY') ?: '');

define('CORS_ORIGINS', getenv('CORS_ORIGINS') ?: 'http://hub.absenin.test');

require_once ROOT_DIR . '/config/constants.php';
