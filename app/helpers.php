<?php
/**
 * Small shared helpers.
 */

/** HTML-escape. */
function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build a URL under the app base path. */
function url(string $path = ''): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

/** URL for an uploaded file (stored value is a path relative to /uploads). */
function upload_url(?string $rel): string
{
    return $rel ? UPLOAD_URL . '/' . ltrim($rel, '/') : '';
}

/** Redirect and stop. */
function redirect(string $path): void
{
    header('Location: ' . (preg_match('~^https?://~', $path) ? $path : url($path)));
    exit;
}

/** Read a request value with a default. */
function input(string $key, $default = null)
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/** A URL-safe slug from arbitrary text. */
function slugify(string $text): string
{
    $text = trim($text);
    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: $text;
    } else {
        $text = mb_strtolower($text, 'UTF-8');
    }
    $text = preg_replace('~[^a-z0-9]+~', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item';
}

/** One-shot flash messages stored in session. */
function flash(string $type, ?string $msg = null)
{
    if ($msg === null) {
        $m = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $m;
    }
    $_SESSION['_flash'][$type] = $msg;
}

/** Format a tour's date or date range for display. */
function format_tour_dates(?string $start, ?string $end, string $lang = 'en'): string
{
    if (!$start) {
        return '';
    }
    $fmt = static function (string $d): string {
        $ts = strtotime($d);
        return $ts ? date('M j, Y', $ts) : $d;
    };
    if (!$end || $end === $start) {
        return $fmt($start);
    }
    // Same month/year -> "Aug 3 – 9, 2025"
    if (date('Y-m', strtotime($start)) === date('Y-m', strtotime($end))) {
        return date('M j', strtotime($start)) . ' – ' . date('j, Y', strtotime($end));
    }
    return $fmt($start) . ' – ' . $fmt($end);
}
