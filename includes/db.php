<?php
/**
 * ARMAS Database Connection
 * PDO MySQL connection with error handling
 */

$host = 'sql207.infinityfree.com';  // check your actual host in VistaPanel > MySQL Databases
$dbname = 'if0_42292012_armas';
$username = 'if0_42292012';         // usually same as your account username
$password = 'your_db_password';     // the password you set when creating the DB

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('ARMAS Database Connection Failed: ' . $e->getMessage());
}
?>