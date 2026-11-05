<?php
// $logFile = fopen(__DIR__ . '/app.log', 'a') or die("Unable to open file!");
// $txt = "Jane Doe\n";
// // fwrite($logFile, $txt);
// fclose($logFile);

// $myfile = fopen(__DIR__ . '/app.log', "r") or die("Unable to open file!");
// // echo fread($myfile, filesize(__DIR__ . '/app.log'));
// fclose($myfile);

function clearLog(): void
{
    $clearLogFile = fopen(__DIR__ . '/app.log', 'w') or die("Unable to open file!");
    $txt = "Log File \n";
    fwrite($clearLogFile, $txt);
    fclose($clearLogFile);
}

function logAction(string $user, string $action): void
{
    $logFile = fopen(__DIR__ . '/app.log', 'a') or die("Unable to open file!");
    $txt = date('Y-m-d H:i:s') . " - " . htmlspecialchars($user) . " - " . htmlspecialchars($action) . "\n";
    fwrite($logFile, $txt);
    fclose($logFile);
}
