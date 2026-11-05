<?php
declare(strict_types=1);

/**
 * GridWatch 2030 REST API (alleen lezen).
 * /api/<route> wordt door web/.htaccess omgezet naar api.php?route=<route>
 */

define('GW_JSON', true);
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function out($data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$route  = trim((string) ($_GET['route'] ?? ''), '/');
$route  = preg_replace('/[^A-Za-z0-9_\-\/]/', '', $route);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

set_exception_handler(function (Throwable $e) use ($route, $pdo) {
    log_msg('ERROR', "Unhandled exception on /api/$route: " . $e->getMessage());
    log_event($pdo, 'ERROR', '-', "Unhandled exception on /api/$route");
    out(['error' => 'Internal server error'], 500);
});

log_msg('INFO', "$method /api/$route from " . client_ip());

if ($method !== 'GET') {
    out(['error' => 'Method not allowed'], 405);
}

if ($route === 'stations') {
    out($pdo->query('SELECT id, name, location, status FROM stations ORDER BY id')->fetchAll());
}

if (preg_match('#^station/(\d+)$#', $route, $m)) {
    $st = $pdo->prepare('SELECT id, name, location, status FROM stations WHERE id = ?');
    $st->execute([(int) $m[1]]);
    $station = $st->fetch();
    if (!$station) {
        out(['error' => 'Station not found'], 404);
    }
    $last = $pdo->prepare(
        'SELECT power_mw, temperature, timestamp FROM sensor_data WHERE station_id = ? ORDER BY id DESC LIMIT 1'
    );
    $last->execute([$station['id']]);
    $row = $last->fetch();
    $station['laatste_meting'] = $row ? [
        'vermogen_mw' => (float) $row['power_mw'],
        'temperatuur' => (float) $row['temperature'],
        'timestamp'   => $row['timestamp'],
    ] : null;
    out($station);
}

if ($route === 'alerts') {
    out($pdo->query('SELECT id, timestamp, type, location, description FROM events ORDER BY id DESC LIMIT 50')->fetchAll());
}

if ($route === 'sensor-data') {
    $rows = $pdo->query(
        'SELECT s.name AS station, d.power_mw AS vermogen_mw, d.temperature AS temperatuur, s.status
         FROM sensor_data d
         JOIN (SELECT station_id, MAX(id) AS mid FROM sensor_data GROUP BY station_id) m ON d.id = m.mid
         JOIN stations s ON s.id = d.station_id
         ORDER BY s.id'
    )->fetchAll();
    foreach ($rows as &$r) {
        $r['vermogen_mw'] = (float) $r['vermogen_mw'];
        $r['temperatuur'] = (float) $r['temperatuur'];
    }
    out($rows);
}

if ($route === 'grid-health') {
    $counts = [];
    foreach ($pdo->query('SELECT status, COUNT(*) AS n FROM stations GROUP BY status')->fetchAll() as $r) {
        $counts[mb_strtolower($r['status'])] = (int) $r['n'];
    }
    $total     = array_sum($counts);
    $offline   = ($counts['niet operationeel'] ?? 0) + ($counts['offline'] ?? 0);
    $storingen = ($counts['waarschuwing'] ?? 0) + ($counts['actieve storing'] ?? 0);
    $status    = ($storingen || $offline) ? 'WAARSCHUWING' : 'OK';
    if ($total && ($storingen + $offline) / $total >= 0.5) {
        $status = 'KRITIEK';
    }
    out([
        'stations_online'  => $total - $offline,
        'stations_offline' => $offline,
        'storingen'        => $storingen,
        'status'           => $status,
    ]);
}

out(['error' => 'Not found'], 404);
