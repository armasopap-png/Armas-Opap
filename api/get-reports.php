<?php
session_start(); require_once '../includes/db.php'; require_once '../includes/auth.php';
if (!in_array($_SESSION['role'], ['agency', 'admin', 'superadmin'])) { http_response_code(403); exit; }

$agency_id = isset($_GET['agency_id']) ? intval($_GET['agency_id']) : null;
$where = $agency_id ? "WHERE agency_id=$agency_id" : "";

$bar = $pdo->query("SELECT status, COUNT(*) as count FROM cases $where GROUP BY status")->fetchAll();
$pie = $pdo->query("SELECT type, COUNT(*) as count FROM cases $where GROUP BY type")->fetchAll();
$line = $pdo->query("SELECT MONTH(created_at) as month, COUNT(*) as count FROM cases WHERE YEAR(created_at)=YEAR(NOW()) " . ($agency_id ? "AND agency_id=$agency_id" : "") . " GROUP BY MONTH(created_at)")->fetchAll();

header('Content-Type: application/json');
echo json_encode(['bar' => $bar, 'pie' => $pie, 'line' => $line]);
