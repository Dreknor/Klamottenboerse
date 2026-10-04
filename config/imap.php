<?php

return [
    // "imap" im Betrieb; "mailpit" nur lokal zum Testen (Mailpit hat kein IMAP, aber eine REST-API)
    'treiber' => env('IMAP_TREIBER', 'imap'),
    'mailpit_url' => env('MAILPIT_URL', 'http://localhost:8025'),
    // Postfach, das im Backend als Posteingang angezeigt wird (z. B. anmeldung@klamottenboerse.de)
    'host' => env('IMAP_HOST'),
    'port' => (int) env('IMAP_PORT', 993),
    'encryption' => env('IMAP_ENCRYPTION', 'ssl'),
    'validate_cert' => (bool) env('IMAP_VALIDATE_CERT', true),
    'username' => env('IMAP_USERNAME'),
    'password' => env('IMAP_PASSWORD'),
    'ordner_spam' => env('IMAP_ORDNER_SPAM', 'Junk'),
    'ordner_papierkorb' => env('IMAP_ORDNER_PAPIERKORB', 'Trash'),
];
