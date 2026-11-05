<?php
declare(strict_types=1);

/**
 * Gedeelde logfuncties.
 * Formaat: "2026-04-01 09:14:22 INFO User admin logged in"
 */

date_default_timezone_set(getenv('TZ') ?: 'Europe/Amsterdam');

function log_msg(string $level, string $msg): void
{
    $file = getenv('LOG_FILE') ?: dirname(__DIR__, 2) . '/logs/app.log';
    $msg  = str_replace(["\r", "\n"], ' ', $msg); // voorkomt log-injectie
    @file_put_contents($file, date('Y-m-d H:i:s') . " $level $msg\n", FILE_APPEND | LOCK_EX);
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '-';
}

/** Sla een gebeurtenis op in de events-tabel (mag een request nooit laten falen). */
function log_event(PDO $pdo, string $type, string $location, string $description): void
{
    try {
        $pdo->prepare('INSERT INTO events (type, location, description) VALUES (?, ?, ?)')
            ->execute([mb_substr($type, 0, 99), mb_substr($location, 0, 99), mb_substr($description, 0, 255)]);
    } catch (Throwable $e) {
        log_msg('ERROR', 'Could not write event: ' . $e->getMessage());
    }
}
