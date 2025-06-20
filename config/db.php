<?php
$host = "sql300.infinityfree.com"; // Replace with your host
$dbname = "if0_39283096_assetManagement";
$username = "if0_39283096";
$password = "9r4VpPux22G";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
