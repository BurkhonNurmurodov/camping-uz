<?php
/**
 * CSRF protection. Token lives in the session; verified on every POST.
 */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Hidden input for forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** True if the submitted token matches. */
function csrf_check(): bool
{
    $sent = $_POST['_csrf'] ?? '';
    return is_string($sent) && !empty($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $sent);
}

/** Abort with 419 on a bad/missing token (call at the top of POST handlers). */
function csrf_verify(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check()) {
        http_response_code(419);
        exit('Invalid or expired session token. Please reload and try again.');
    }
}
