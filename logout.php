<?php

session_start();

// Vernietig alle sessiedata
$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
