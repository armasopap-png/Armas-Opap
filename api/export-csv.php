<?php
session_start(); require_once '../includes/db.php'; require_once '../includes/auth.php';
if (!in_array($_SESSION['role'], ['agency', 'admin', 'superadmin'])) { http_response_code(403); exit; }

$agency_id = isset($_GET['agency_id']) ? intval($_GET['agency_id']) : null;
$sql = "SELECT c.case_number, CONCAT(o.last_name,', ',o.first_name) as ofw_name, c.type, c.status, c.location_abroad, c.employer_name, c.date_of_departure, c.created_at, c.updated_at FROM cases c JOIN ofws o ON c.ofw_id=o.id WHERE 1=1";
$params = [];
if ($agency_id) { $sql .= " AND c.agency_id=?"; $params[] = $agency_id; }
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();

header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="ARMAS-Cases-Export-' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Case Number','OFW Name','Type','Status','Location Abroad','Employer','Departure Date','Date Filed','Last Updated']);
foreach ($rows as $row) fputcsv($out, $row); fclose($out); exit;
