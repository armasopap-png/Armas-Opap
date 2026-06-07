<?php
/**
 * ARMAS Authentication Helper
 * Call at top of every protected page
 */

function require_auth($required_role) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: /armas/pages/login.php');
        exit;
    }
    
    if ($_SESSION['role'] !== $required_role) {
        header('Location: /armas/403.php');
        exit;
    }
    
    if ($_SESSION['status'] !== 'active') {
        session_destroy();
        header('Location: /armas/pages/login.php?error=inactive');
        exit;
    }
}

function require_any_auth($roles) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: /armas/pages/login.php');
        exit;
    }
    
    if (!in_array($_SESSION['role'], $roles)) {
        header('Location: /armas/403.php');
        exit;
    }
    
    if ($_SESSION['status'] !== 'active') {
        session_destroy();
        header('Location: /armas/pages/login.php?error=inactive');
        exit;
    }
}
?>
