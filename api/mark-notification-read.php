<?php
/**
 * ARMAS API — Mark notification(s) as read
 */
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$user_id = (int) $_SESSION['user_id'];

try {
    if (!empty($data['mark_all'])) {
        $stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL");
        $stmt->execute([$user_id]);
    } elseif (!empty($data['notif_id'])) {
        $stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL");
        $stmt->execute([(int)$data['notif_id'], $user_id]);
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}