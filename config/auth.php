<?php
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . "/app.php";

function is_hashed_password(string $hash): bool {
    return preg_match('/^(\$2y\$|\$2a\$|\$argon2i\$|\$argon2id\$)/', $hash) === 1;
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(PDO $pdo, array $user, string $password): bool {
    if (empty($user['password'])) {
        return false;
    }

    if (is_hashed_password((string)$user['password'])) {
        return password_verify($password, (string)$user['password']);
    }

    $plainMatch = hash_equals((string)$user['password'], $password);
    if ($plainMatch) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $user['id']]);
    }

    return $plainMatch;
}

function require_login() {
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function require_alumni() {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'alumni') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function require_employer() {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'employer') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function require_alumni_officer() {
    require_login();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'alumni_officer') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}