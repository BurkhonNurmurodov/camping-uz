<?php
/**
 * Single include that every entry point pulls in first.
 * Loads config, starts the session, and wires up the helpers.
 */

require __DIR__ . '/config.php';

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

// Baseline security headers (skip on CLI / when output already started).
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 0');
    header_remove('X-Powered-By');
}

// Secure-ish session cookie defaults.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('campinguz');
    session_start();
}

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/i18n.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/upload.php';
require __DIR__ . '/richtext.php';
require __DIR__ . '/telegram.php';
