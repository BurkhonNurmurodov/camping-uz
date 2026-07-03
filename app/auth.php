<?php
/**
 * Admin authentication: session login with a simple lockout throttle.
 */

function admin_user(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return db_one('SELECT id, username, display_name, email FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
}

function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

/** Gate an admin page; bounce to login if not signed in. */
function require_admin(): void
{
    if (!is_admin()) {
        $loginUrl = defined('IS_ADMIN_APP') ? url('login') : url('admin/login');
        $_SESSION['_after_login'] = $_SERVER['REQUEST_URI'] ?? (defined('IS_ADMIN_APP') ? url('') : url('admin/'));
        redirect($loginUrl);
    }
}

/**
 * Attempt a login. Returns [bool ok, ?string error].
 * Throttles to 5 failed attempts per 10 minutes per session.
 */
function admin_login(string $username, string $password): array
{
    $now    = time();
    $window = 600;
    $max    = 5;

    $att = $_SESSION['_login_attempts'] ?? ['count' => 0, 'first' => $now];
    if ($now - $att['first'] > $window) {
        $att = ['count' => 0, 'first' => $now]; // window expired
    }
    if ($att['count'] >= $max) {
        $wait = ceil(($window - ($now - $att['first'])) / 60);
        return [false, "Too many attempts. Try again in {$wait} min."];
    }

    $row = db_one('SELECT id, password_hash FROM admins WHERE username = ?', [$username]);
    if ($row && password_verify($password, $row['password_hash'])) {
        // success
        unset($_SESSION['_login_attempts']);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $row['id'];

        // transparently upgrade legacy hashes
        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            db_run('UPDATE admins SET password_hash = ? WHERE id = ?',
                [password_hash($password, PASSWORD_DEFAULT), $row['id']]);
        }
        return [true, null];
    }

    $att['count']++;
    $_SESSION['_login_attempts'] = $att;
    return [false, 'Incorrect username or password.'];
}

function admin_logout(): void
{
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
}

/** Change the signed-in admin's username/password. */
function admin_update_credentials(int $id, string $username, ?string $newPassword): void
{
    if ($newPassword !== null && $newPassword !== '') {
        db_run('UPDATE admins SET username = ?, password_hash = ? WHERE id = ?',
            [$username, password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    } else {
        db_run('UPDATE admins SET username = ? WHERE id = ?', [$username, $id]);
    }
}
