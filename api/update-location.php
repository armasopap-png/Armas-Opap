<?php

/**
 * ARMAS API — Update OFW Location
 * Called via JS from OFW dashboard after login (browser Geolocation API)
 */
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ofw') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$lat = isset($data['latitude']) ? floatval($data['latitude']) : null;
$lng = isset($data['longitude']) ? floatval($data['longitude']) : null;

if ($lat === null || $lng === null || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
    exit;
}

$now = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("UPDATE ofws SET latitude=?, longitude=?, location_updated_at=? WHERE user_id=?");
$stmt->execute([$lat, $lng, $now, $_SESSION['user_id']]);

echo json_encode(['success' => true, 'message' => 'Location updated']);