<?php
declare(strict_types=1);

// Acties voor de scenario-knoppen (alleen voor ingelogde gebruikers).
define('GW_JSON', true);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function reply(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if (!isLoggedIn()) {
    reply(['error' => 'Not logged in'], 401);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    reply(['error' => 'Method not allowed'], 405);
}

$in     = json_decode((string) file_get_contents('php://input'), true) ?: [];
$action = (string) ($in['action'] ?? '');

if ($action === 'error') {
    log_msg('ERROR', 'Test exception generated');
    log_event($pdo, 'ERROR', '-', 'Test exception generated');
    reply(['error' => 'Test exception generated'], 500);
}

if ($action === 'records') {
    $count = max(1, min((int) ($in['count'] ?? 1000), 100000));
    $ids   = $pdo->query('SELECT id FROM stations')->fetchAll(PDO::FETCH_COLUMN);
    if (!$ids) {
        reply(['error' => 'Geen stations aanwezig'], 400);
    }

    $pdo->beginTransaction();
    for ($done = 0; $done < $count; $done += 500) {
        $n      = min(500, $count - $done);
        $params = [];
        for ($i = 0; $i < $n; $i++) {
            array_push($params, $ids[array_rand($ids)], mt_rand(500, 2500) / 10, mt_rand(250, 850) / 10);
        }
        $pdo->prepare(
            'INSERT INTO sensor_data (station_id, power_mw, temperature) VALUES '
            . implode(',', array_fill(0, $n, '(?,?,?)'))
        )->execute($params);
    }
    $pdo->commit();

    log_msg('INFO', "Generated $count sensor records");
    log_event($pdo, 'LOAD_TEST', '-', "Generated $count sensor records");
    reply(['created' => $count]);
}

reply(['error' => 'Unknown action'], 400);
