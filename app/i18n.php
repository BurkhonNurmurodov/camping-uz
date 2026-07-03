<?php
/**
 * Bilingual (EN/RU) support.
 *  - UI strings come from lang/{lang}.php
 *  - DB content uses *_en / *_ru columns, picked via lang_field()
 *
 * Language is chosen via ?lang=, remembered in a cookie, default from config.
 */

function supported_langs(): array
{
    return array_map('trim', explode(',', SUPPORTED_LANGS));
}

function current_lang(): string
{
    static $lang = null;
    if ($lang !== null) {
        return $lang;
    }
    $supported = supported_langs();

    $req = $_GET['lang'] ?? null;
    if ($req && in_array($req, $supported, true)) {
        $lang = $req;
        setcookie('lang', $lang, [
            'expires'  => time() + 60 * 60 * 24 * 365,
            'path'     => '/',
            'samesite' => 'Lax',
        ]);
        return $lang;
    }
    if (!empty($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supported, true)) {
        return $lang = $_COOKIE['lang'];
    }
    return $lang = DEFAULT_LANG;
}

/** Translate a UI string key for the current language. */
function t(string $key, ?string $fallback = null): string
{
    static $dict = null;
    if ($dict === null) {
        $file = APP_ROOT . '/lang/' . current_lang() . '.php';
        $dict = is_file($file) ? (require $file) : [];
    }
    return $dict[$key] ?? ($fallback ?? $key);
}

/**
 * Pick a localized DB field from a row: lang_field($row, 'title')
 * returns title_ru when the language is RU and it's non-empty, else title_en.
 */
function lang_field(array $row, string $base, ?string $lang = null)
{
    $lang = $lang ?: current_lang();
    $val  = $row[$base . '_' . $lang] ?? null;
    if ($val === null || $val === '') {
        $val = $row[$base . '_en'] ?? null; // fall back to English
    }
    return $val;
}

/** Build the current URL with a different ?lang= value (for the switcher). */
function lang_switch_url(string $lang): string
{
    $params = $_GET;
    $params['lang'] = $lang;
    $qs = http_build_query($params);
    return strtok($_SERVER['REQUEST_URI'], '?') . ($qs ? '?' . $qs : '');
}
