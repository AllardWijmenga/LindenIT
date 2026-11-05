<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logging/logging.php';

logAction("INFO - " . "ip " . $_SERVER['REMOTE_ADDR'] . " - " . (isset($_SESSION['name']) ? $_SESSION['name'] : 'Unknown User'), 'Logged out.');

session_start();

// Vernietig alle sessiedata
$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
