<?php
// authentication functions

// starts session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if already logged in
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

// Redirect to login page if not logged in
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: LindenFrontPage.php');
        exit;
    }
}

// get current user data
function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
    ];
}
