<?php
// Database connectie (PDO)
require_once __DIR__ . '/logger.php';

// In Docker komen deze waarden uit docker-compose.yml; zonder Docker gelden de oude waarden.
$db_host = getenv('DB_HOST')     ?: 'localhost';
$db_name = getenv('DB_NAME')     ?: 'gridwatch';
$db_user = getenv('DB_USER')     ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';

// assume database connection successful, else catch the error
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ]
    );
} catch (PDOException $e) {
    log_msg('ERROR', 'Database connection failed: ' . $e->getMessage());
    http_response_code(503);
    if (defined('GW_JSON')) {
        // de API antwoordt altijd met JSON
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode(['error' => 'Database not available']));
    }
    die('Het spijt ons, er was een fout opgetreden tijdens het laden van deze website. ERROR code: 277288: ' . htmlspecialchars($e->getMessage()));
}
