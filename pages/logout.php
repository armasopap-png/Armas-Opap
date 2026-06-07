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
}

session_destroy();
header('Location: /armas/pages/login.php');
exit;
