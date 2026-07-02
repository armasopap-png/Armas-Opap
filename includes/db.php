<?php
$host = "localhost";
$dbname = "armas"; // Replace with your local database name
$username = "root";
$password = "";    // Default XAMPP password is empty

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>