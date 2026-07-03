<?php
require __DIR__ . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index#contact');
}
csrf_verify();

// Honeypot: real users leave it empty.
if (trim((string) input('website', '')) !== '') {
    redirect('index?sent=1#contact');
}

$first = trim((string) input('first_name', ''));
$last  = trim((string) input('last_name', ''));
$email = trim((string) input('email', ''));
$topic = trim((string) input('topic', ''));
$msg   = trim((string) input('message', ''));

if ($first === '' || $last === '' || $msg === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('index?err=1#contact');
}

db_run(
    'INSERT INTO contact_messages (first_name, last_name, email, topic, message) VALUES (?,?,?,?,?)',
    [$first, $last, $email, $topic ?: null, $msg]
);

telegram_notify(implode("\n", [
    '✉️ <b>New contact message</b>',
    'From: ' . tg_escape($first . ' ' . $last) . ' &lt;' . tg_escape($email) . '&gt;',
    'Topic: ' . tg_escape($topic ?: '—'),
    '',
    tg_escape(mb_strimwidth($msg, 0, 500, '…')),
]));

redirect('index?sent=1#contact');
