<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Get distinct departments from asset table
            $stmt = $pdo->query("
                SELECT DISTINCT department 
                FROM asset 
                WHERE department IS NOT NULL 
                AND TRIM(department) != '' 
                ORDER BY department ASC
            ");
            $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Return as simple array of strings (like your original code)
            echo json_encode($departments);
            break;
            
        case 'POST':
        case 'PUT':
        case 'PATCH':
        case 'DELETE':
            http_response_code(405);
            echo json_encode([
                'error' => 'Method not allowed',
                'message' => 'Departments are managed automatically from asset data'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
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
?>
