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

    $requestMethod = $_SERVER['REQUEST_METHOD'];
    
    // ✅ FIX: Get ID from $_GET (set by route.php) instead of parsing URL
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    // Validate ID is numeric if present
    if ($id && !is_numeric($id)) {
        $id = null;
    }

    switch ($requestMethod) {
        case 'GET':
            if ($id) {
                // GET single asset with category name
                $stmt = $pdo->prepare("
                    SELECT a.*, c.name as category_name 
                    FROM asset a 
                    LEFT JOIN category c ON a.category_id = c.id 
                    WHERE a.id = :id
                ");
                $stmt->execute([':id' => $id]);
                $asset = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($asset) {
                    echo json_encode($asset);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Asset not found']);
                }
            } else {
                // GET all assets with category names
                $stmt = $pdo->query("
                    SELECT a.*, c.name as category_name 
                    FROM asset a 
                    LEFT JOIN category c ON a.category_id = c.id 
                    ORDER BY a.id DESC
                ");
                $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($assets);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if JSON decode was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                break;
            }
            
            // Validate input
            $required = ['name', 'category_id', 'department'];
            $missing = array_diff($required, array_keys($input ?? []));
            
            if (!empty($missing)) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Missing required fields',
                    'missing' => array_values($missing)
                ]);
                break;
            }

            // Validate category_id exists
            $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :category_id");
            $stmt->execute([':category_id' => $input['category_id']]);
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid category ID']);
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO asset 
                (name, category_id, department, status, purchase_date, warranty_expiry, value, usage_type) 
                VALUES 
                (:name, :category_id, :department, :status, :purchase_date, :warranty_expiry, :value, :usage_type)");
            
            $stmt->execute([
                ':name' => $input['name'],
                ':category_id' => $input['category_id'],
                ':department' => $input['department'],
                ':status' => $input['status'] ?? 'In Use',
                ':purchase_date' => $input['purchaseDate'] ?? date('Y-m-d'),
                ':warranty_expiry' => $input['warranty_expiry'] ?? null,
                ':value' => $input['value'] ?? 0,
                ':usage_type' => $input['usage_type'] ?? 'General'
            ]);

            $newId = $pdo->lastInsertId();

            // Fetch the newly created asset with category_name
            $stmt = $pdo->prepare("
                SELECT a.*, c.name AS category_name 
                FROM asset a 
                LEFT JOIN category c ON a.category_id = c.id 
                WHERE a.id = :id
            ");
            $stmt->execute([':id' => $newId]);
            $newAsset = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($newAsset) {
                http_response_code(201);
                echo json_encode($newAsset);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to fetch created asset']);
            }
            break;

        case 'PUT':
        case 'PATCH':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Asset ID required']);
                break;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                break;
            }
            
            // Build update query dynamically
            $allowedFields = ['name', 'category_id', 'department', 'status', 
                            'purchase_date', 'warranty_expiry', 'value', 'usage_type'];
            $updates = [];
            $params = [':id' => $id];
            
            foreach ($input as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    // Validate category_id if it's being updated
                    if ($key === 'category_id') {
                        $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :category_id");
                        $stmt->execute([':category_id' => $value]);
                        if (!$stmt->fetch()) {
                            http_response_code(400);
                            echo json_encode(['error' => 'Invalid category ID']);
                            break 2; // Break out of both foreach and switch
                        }
                    }
                    $updates[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }
            
            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['error' => 'No valid fields to update']);
                break;
            }

            $query = "UPDATE asset SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            // Return updated asset with category name
            $stmt = $pdo->prepare("
                SELECT a.*, c.name as category_name 
                FROM asset a 
                LEFT JOIN category c ON a.category_id = c.id 
                WHERE a.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $updatedAsset = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($updatedAsset) {
                http_response_code(200);
                echo json_encode($updatedAsset);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Asset not found']);
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Asset ID required']);
                break;
            }

            // First check if asset exists
            $stmt = $pdo->prepare("SELECT id FROM asset WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Asset not found']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM asset WHERE id = :id");
            $stmt->execute([':id' => $id]);

            http_response_code(200);
            echo json_encode(['message' => 'Asset deleted successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'error' => 'Method not allowed',
            ]);
            error_log("405 Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
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
