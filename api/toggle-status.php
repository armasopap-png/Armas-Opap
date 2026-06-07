<?php
session_start(); require_once '../includes/db.php'; require_once '../includes/auth.php';
if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) { http_response_code(403); exit; }

$user_id = intval($_POST['user_id']);
$new_status = $_POST['new_status'] === 'active' ? 'active' : 'inactive';
$pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new_status, $user_id]);
log_audit($pdo, $_SESSION['user_id'], 'TOGGLE_STATUS_' . strtoupper($new_status), 'users', $user_id);
echo json_encode(['success' => true, 'new_status' => $new_status]);
