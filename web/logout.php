<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';

if (isLoggedIn()) {
    log_msg('INFO', 'User ' . ($_SESSION['user_name'] ?? '?') . ' logged out');
}

// Vernietig alle sessiedata
$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
