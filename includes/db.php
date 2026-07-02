<?php
/**
 * ARMAS Database Connection
 * PDO MySQL connection with error handling
 */

$host = 'sql300.infinityfree.com';
$dbname = 'if0_42292833_armas';
$username = 'if0_42292833';
$password = 'BLYTIODhF8KFhZ';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('ARMAS Database Connection Failed: ' . $e->getMessage());
}
?>