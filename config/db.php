<?php
$db_host = "sql12.freesqldatabase.com";
$port = 3306; // Default MySQL port. Replace if your provider uses a different port.
$db_name = "sql12786002";
$db_user = "sql12786002";
$db_pass = "nbGpSqimCX";

try {
    $pdo = new PDO("mysql:host=$db_host;port=$port;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
