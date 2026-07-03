<?php
/**
 * Silk Naviora — configuration.
 *
 * Real secrets go in app/config.local.php (git-ignored). It is loaded FIRST so
 * its define()s win; the guarded defaults below only fill in what it omits.
 */

// Local overrides (DB creds, telegram, base path …) — loaded before defaults.
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// ---- Database ----
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'camping_uz');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// ---- Site ----
// Base URL path the app is served from (no trailing slash). "" if at domain root.
if (!defined('BASE_PATH')) define('BASE_PATH', getenv('BASE_PATH') ?: '');
if (!defined('DEFAULT_LANG')) define('DEFAULT_LANG', 'en');
if (!defined('SUPPORTED_LANGS')) define('SUPPORTED_LANGS', 'en,ru');

// ---- Default admin credentials (used only when seeding an empty DB) ----
if (!defined('DEFAULT_ADMIN_USER')) define('DEFAULT_ADMIN_USER', 'admin');
if (!defined('DEFAULT_ADMIN_PASS')) define('DEFAULT_ADMIN_PASS', 'password');

// ---- Paths ----
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));               // camping-uz/
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', APP_ROOT . '/uploads');        // filesystem
if (!defined('UPLOAD_URL')) define('UPLOAD_URL', getenv('UPLOAD_URL') ?: BASE_PATH . '/uploads');       // web

// ---- Security ----
if (!defined('APP_DEBUG')) define('APP_DEBUG', true); // set false in production
