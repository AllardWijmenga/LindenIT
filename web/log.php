<?php
declare(strict_types=1);

// Geeft het logbestand terug als tekst voor het tabblad "Log" (alleen voor ingelogde gebruikers).
// Het logbestand staat buiten de webroot (/logs), dus de browser kan het niet rechtstreeks openen.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

if (!isLoggedIn()) {
    http_response_code(401);
    exit('Not logged in');
}

$file = log_file();
if (!is_readable($file)) {
    exit;
}

// Laatste 500 regels, zodat de pagina snel blijft bij een groot logbestand.
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
echo implode("\n", array_slice($lines, -500));
