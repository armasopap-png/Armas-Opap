<?php
/**
 * ARMAS Logout Page
 */
session_start();
if (isset($_SESSION['user_id'])) {
    require_once '../includes/db.php';

    // Log logout to audit
    $pdo->prepare("INSERT INTO audit_logs (actor_id, action, ip_address) VALUES (?,?,?)")
        ->execute([$_SESSION['user_id'], 'LOGOUT', $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN']);

    // Invalidate any remember-me token in the DB so the cookie can't be reused
    $pdo->prepare("UPDATE users SET remember_token=NULL, remember_expires=NULL WHERE id=?")
        ->execute([$_SESSION['user_id']]);
}

// Clear the remember-me cookie itself (must match path used when it was set)
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE['remember_me']);
}

session_destroy();
header('Location: /armas/pages/login.php');
exit;