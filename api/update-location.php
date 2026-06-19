<?php

/**
 * ARMAS API — Update OFW Location
 * Called via JS from OFW dashboard on login / periodically while the OFW
 * is active (browser Geolocation API). Fires on every device the OFW logs
 * into, so the latest device to report a position "wins" — which is the
 * desired behavior for tracking where the OFW currently is.
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

// rowCount() is 0 if no ofws row matches this user_id (e.g. orphaned/missing
// profile row) — previously this still reported success, masking the bug.
if ($stmt->rowCount() === 0) {
    $check = $pdo->prepare("SELECT id FROM ofws WHERE user_id=?");
    $check->execute([$_SESSION['user_id']]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'No OFW profile linked to this account']);
        exit;
    }
    // Row exists but values were identical (no actual change) — still a
    // success, just nothing to update besides the timestamp below.
    $pdo->prepare("UPDATE ofws SET location_updated_at=? WHERE user_id=?")
        ->execute([$now, $_SESSION['user_id']]);
}

echo json_encode(['success' => true, 'message' => 'Location updated']);