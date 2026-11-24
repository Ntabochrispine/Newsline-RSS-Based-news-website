<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $secureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/functions.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentUserId(): ?int
{
    return isset($_SESSION['user_id'])
        ? (int) $_SESSION['user_id']
        : null;
}

function currentUserRole(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}

function requireRole(array $allowedRoles): void
{
    requireLogin();

    if (!in_array(currentUserRole(), $allowedRoles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
