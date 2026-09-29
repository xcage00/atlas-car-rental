<?php
declare(strict_types=1);

/* ---------- Database ---------- */
define('DB_HOST', getenv('ATLAS_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('ATLAS_DB_NAME') ?: 'atlas_car_rental');
define('DB_USER', getenv('ATLAS_DB_USER') ?: 'root');
define('DB_PASS', getenv('ATLAS_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/* ---------- Base URL ---------- */
define('BASE_URL', '/atlas-car-rental');

/* ---------- Paths ---------- */
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads');
define('UPLOAD_URL',  BASE_URL . '/assets/uploads');
define('SAMPLE_URL',  BASE_URL . '/assets/uploads/sample');
define('PLACEHOLDER_IMG', BASE_URL . '/assets/img/car-placeholder.svg');

/* ---------- Site ---------- */
define('SITE_NAME', 'Atlas Automotive Services');
define('SITE_SHORT', 'Atlas');
define('CURRENCY', 'RWF');
define('TIMEZONE', 'Africa/Kigali');

date_default_timezone_set(TIMEZONE);

/* ---------- Errors ---------- */
$debugMode = filter_var(getenv('ATLAS_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debugMode ? '1' : '0');
error_reporting(E_ALL);

/* ---------- Session ---------- */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}
