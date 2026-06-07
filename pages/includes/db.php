<?php
/**
 * ARMAS Database Connection
 * PDO MySQL connection with error handling
 */

$host = 'localhost';
$dbname = 'armas_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('ARMAS Database Connection Failed: ' . $e->getMessage());
}
?>
