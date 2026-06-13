<?php

/**
 * ARMAS API — Get OFW Locations
 * Returns OFW list with last login and GPS coordinates
 * Accessible by: superadmin, admin, agency (own OFWs only)
 */
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['superadmin', 'admin', 'agency'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    if ($_SESSION['role'] === 'agency') {
        // Agency: only their assigned OFWs
        $agency_stmt = $pdo->prepare("SELECT id FROM agencies WHERE user_id = ?");
        $agency_stmt->execute([$_SESSION['user_id']]);
        $agency = $agency_stmt->fetch();
        if (!$agency) {
            echo json_encode(['success' => false, 'message' => 'Agency not found']);
            exit;
        }
        $stmt = $pdo->prepare("
            SELECT o.id, o.first_name, o.last_name, o.country, o.city,
                   o.latitude, o.longitude, o.location_updated_at,
                   u.email, u.last_login, u.last_login_ip, u.status
            FROM ofws o
            JOIN users u ON o.user_id = u.id
            WHERE o.agency_id = ?
            ORDER BY o.last_name ASC
        ");
        $stmt->execute([$agency['id']]);
    } else {
        // Admin / Superadmin: all OFWs
        $stmt = $pdo->query("
            SELECT o.id, o.first_name, o.last_name, o.country, o.city,
                   o.latitude, o.longitude, o.location_updated_at,
                   u.email, u.last_login, u.last_login_ip, u.status,
                   a.name as agency_name
            FROM ofws o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN agencies a ON o.agency_id = a.id
            ORDER BY o.last_name ASC
        ");
    }

    $ofws = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $ofws]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}