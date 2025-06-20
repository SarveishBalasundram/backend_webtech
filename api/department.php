<?php
require_once __DIR__ . '/../config/cors.php'; // ✅ must be first
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get distinct departments from asset table
        $stmt = $pdo->query("
            SELECT DISTINCT department 
            FROM asset 
            WHERE department IS NOT NULL AND TRIM(department) != '' 
            ORDER BY department ASC
        ");
        $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($departments);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
}
