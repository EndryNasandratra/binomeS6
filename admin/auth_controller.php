<?php

require_once __DIR__ . '/db.php';

function ensureSessionStarted(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function isAdminAuthenticated(): bool
{
    ensureSessionStarted();
    return isset($_SESSION['admin_user']);
}

function authenticateAdmin(string $username, string $password): array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return [
            'success' => false,
            'error' => 'Veuillez remplir tous les champs.',
        ];
    }

    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [
            'success' => false,
            'error' => 'Connexion a la base impossible. Verifiez vos conteneurs Docker.',
        ];
    }

    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return [
            'success' => false,
            'error' => 'Identifiants invalides.',
        ];
    }

    ensureSessionStarted();
    session_regenerate_id(true);
    $_SESSION['admin_user'] = [
        'id' => (int) $user['id'],
        'username' => $user['username'],
    ];

    return [
        'success' => true,
        'error' => '',
    ];
}

function requireAdminAuthentication(string $redirect = 'index.php'): void
{
    if (!isAdminAuthenticated()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function logoutAdmin(string $redirect = 'index.php'): void
{
    ensureSessionStarted();

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

    header('Location: ' . $redirect);
    exit;
}
