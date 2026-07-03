<?php
/**
 * Key/value settings access (cached per request).
 */

function settings_all(bool $reload = false): array
{
    static $cache = null;
    if ($cache === null || $reload) {
        $cache = [];
        foreach (db_all('SELECT `key`, `value` FROM settings') as $r) {
            $cache[$r['key']] = $r['value'];
        }
    }
    return $cache;
}

function setting(string $key, $default = null)
{
    $all = settings_all();
    return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== ''
        ? $all[$key]
        : $default;
}

function set_setting(string $key, ?string $value): void
{
    db_run(
        'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
        [$key, $value]
    );
    settings_all(true); // refresh cache
}
