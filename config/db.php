<?php
$host = "sql12.freesqldatabase.com";
$port = 3306; // Default MySQL port. Replace if your provider uses a different port.
$dbname = "sql12786002";
$username = "sql12786002";
$password = "nbGpSqimCX";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
