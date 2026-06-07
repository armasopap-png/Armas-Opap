<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!in_array($_SESSION['role'] ?? '', ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

header('Content-Type: application/json');

// Handle both JSON and POST
$data = json_decode(file_get_contents('php://input'), true);
$user_id = intval($data['user_id'] ?? $_POST['user_id'] ?? 0);
$status  = $data['status'] ?? $_POST['new_status'] ?? '';

$allowed = ['active', 'inactive', 'suspended', 'pending'];
if (!$user_id || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

try {
    $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$status, $user_id]);
    $pdo->prepare("UPDATE agencies SET status = ? WHERE user_id = ?")->execute([$status, $user_id]);
    log_audit($pdo, $_SESSION['user_id'], 'TOGGLE_STATUS_' . strtoupper($status), 'users', $user_id);
    echo json_encode(['success' => true, 'new_status' => $status]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
