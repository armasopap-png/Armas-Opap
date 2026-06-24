<?php
/**
 * ARMAS API — SOS Emergency Alert
 * - Saves alert to sos_alerts table (includes user_id as required by schema)
 * - Updates OFW GPS coordinates (if provided)
 * - Inserts a notification for EVERY agency/admin user
 */
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Only logged-in OFW users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ofw') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$lat     = isset($data['latitude'])  && $data['latitude']  !== null ? floatval($data['latitude'])  : null;
$lng     = isset($data['longitude']) && $data['longitude'] !== null ? floatval($data['longitude']) : null;
$user_id = $_SESSION['user_id'];

try {
    // Get OFW record
    $stmt = $pdo->prepare("SELECT o.id, o.first_name, o.last_name FROM ofws o WHERE o.user_id = ?");
    $stmt->execute([$user_id]);
    $ofw = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ofw) {
        echo json_encode(['success' => false, 'message' => 'OFW record not found']);
        exit;
    }

    // Update GPS coordinates in ofws table if provided
    if ($lat !== null && $lng !== null && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
        $upd = $pdo->prepare("UPDATE ofws SET latitude=?, longitude=?, location_updated_at=NOW() WHERE id=?");
        $upd->execute([$lat, $lng, $ofw['id']]);
    }

    // Build message
    $name     = trim($ofw['first_name'] . ' ' . $ofw['last_name']);
    $loc_text = ($lat !== null && $lng !== null)
        ? " Location: https://maps.google.com/?q=$lat,$lng"
        : ' (GPS location not available)';
    $sos_message = "🚨 SOS EMERGENCY from OFW $name!" . $loc_text;

    // Save SOS alert record — include user_id as required by schema
    $insert = $pdo->prepare("
        INSERT INTO sos_alerts (ofw_id, user_id, latitude, longitude, message, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'active', NOW())
    ");
    $insert->execute([$ofw['id'], $user_id, $lat, $lng, $sos_message]);
    $sos_id = $pdo->lastInsertId();

    // Notify all active agency users
    $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, ofw_id, created_at) VALUES (?, ?, 'sos_emergency', ?, NOW())");
    $notified = 0;

    $agency_users = $pdo->query("SELECT id FROM users WHERE role = 'agency' AND status = 'active'");
    foreach ($agency_users->fetchAll(PDO::FETCH_COLUMN) as $uid) {
        $notif_stmt->execute([$uid, $sos_message, $ofw['id']]);
        $notified++;
    }

    // Notify all active admin/superadmin users
    $admin_users = $pdo->query("SELECT id FROM users WHERE role IN ('admin','superadmin') AND status = 'active'");
    foreach ($admin_users->fetchAll(PDO::FETCH_COLUMN) as $uid) {
        $notif_stmt->execute([$uid, $sos_message, $ofw['id']]);
        $notified++;
    }

    echo json_encode([
        'success'  => true,
        'message'  => 'SOS alert sent',
        'sos_id'   => $sos_id,
        'notified' => $notified,
        'lat'      => $lat,
        'lng'      => $lng
    ]);

} catch (PDOException $e) {
    error_log('SOS Alert Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}