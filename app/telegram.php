<?php
/**
 * Telegram notifications to the admin.
 * No-op (returns false) when a bot token / chat id isn't configured.
 * Never throws — a failed notification must not break a user-facing request.
 */

function telegram_enabled(): bool
{
    return setting('telegram_bot_token') && setting('telegram_chat_id');
}

/**
 * Send a message. Returns ['ok' => bool, 'error' => ?string] so callers
 * (e.g. the Settings test button) can show a useful reason on failure.
 */
function telegram_send(string $text): array
{
    $token = (string) setting('telegram_bot_token', '');
    $chat  = (string) setting('telegram_chat_id', '');
    if ($token === '' || $chat === '') {
        return ['ok' => false, 'error' => 'Telegram bot token / chat id not set.'];
    }

    $url  = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $data = http_build_query([
        'chat_id'                  => $chat,
        'text'                     => $text,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => 'true',
    ]);

    try {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 6,
                CURLOPT_CONNECTTIMEOUT => 4,
            ]);
            $res = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($res === false) {
                return ['ok' => false, 'error' => 'Network error: ' . $err];
            }
        } else {
            $ctx = stream_context_create(['http' => [
                'method'        => 'POST',
                'header'        => 'Content-Type: application/x-www-form-urlencoded',
                'content'       => $data,
                'timeout'       => 6,
                'ignore_errors' => true,
            ]]);
            $res = @file_get_contents($url, false, $ctx);
            if ($res === false) {
                return ['ok' => false, 'error' => 'Network error reaching Telegram.'];
            }
        }

        $json = json_decode((string) $res, true);
        if (is_array($json) && !empty($json['ok'])) {
            return ['ok' => true, 'error' => null];
        }
        return ['ok' => false, 'error' => $json['description'] ?? 'Unexpected Telegram response.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/** Fire-and-forget notify used by public forms; never throws, returns success. */
function telegram_notify(string $text): bool
{
    return telegram_send($text)['ok'];
}

/** Escape user text for Telegram HTML parse mode. */
function tg_escape(string $s): string
{
    return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $s);
}
