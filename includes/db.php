<?php
// Database connectie (PDO)

$db_host = 'localhost';
$db_name = 'gridwatch';
$db_user = 'root';
$db_pass = '';

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
        ]
    );
} catch (PDOException $e) {
    die('Het spijt ons, er was een fout opgetreden tijdens het laden van deze website. ERROR code: 277288: ' . htmlspecialchars($e->getMessage()));
}
