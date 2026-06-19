<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ofw') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized', 'role' => $_SESSION['role'] ?? 'none']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$lat = isset($data['latitude'])  ? floatval($data['latitude'])  : null;
$lng = isset($data['longitude']) ? floatval($data['longitude']) : null;

if ($lat === null || $lng === null || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    echo json_encode(['success' => false, 'message' => 'Invalid coordinates', 'received' => $data]);
    exit;
}

$now = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("UPDATE ofws SET latitude=?, longitude=?, location_updated_at=? WHERE user_id=?");
$ok = $stmt->execute([$lat, $lng, $now, $_SESSION['user_id']]);
$rows = $stmt->rowCount();

echo json_encode([
    'success'       => $ok && $rows > 0,
    'message'       => $rows > 0 ? 'Location updated' : 'No OFW record found for this user',
    'rows_affected' => $rows,
    'lat'           => $lat,
    'lng'           => $lng,
    'timestamp'     => $now
]);