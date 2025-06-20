<?php
require_once __DIR__ . '/../config/cors.php'; // ✅ must be first
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', rtrim($path, '/'));
    $id = null;

    // Extract asset ID
    $assetIndex = array_search('assets', $segments);
    if ($assetIndex !== false && isset($segments[$assetIndex + 1]) && is_numeric($segments[$assetIndex + 1])) {
        $id = $segments[$assetIndex + 1];
    }

    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("
                    SELECT a.*, c.name AS category_name 
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
                $stmt = $pdo->query("
                    SELECT a.*, c.name AS category_name 
                    FROM asset a 
                    LEFT JOIN category c ON a.category_id = c.id 
                    ORDER BY a.id DESC
                ");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                break;
            }

            $required = ['name', 'category_id', 'department'];
            $missing = array_diff($required, array_keys($input ?? []));
            if (!empty($missing)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields', 'missing' => array_values($missing)]);
                break;
            }

            // Validate category_id
            $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :category_id");
            $stmt->execute([':category_id' => $input['category_id']]);
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid category ID']);
                break;
            }

            $stmt = $pdo->prepare("
                INSERT INTO asset (name, category_id, department, status, purchase_date, warranty_expiry, value, usage_type)
                VALUES (:name, :category_id, :department, :status, :purchase_date, :warranty_expiry, :value, :usage_type)
            ");
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

            $id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("
                SELECT a.*, c.name AS category_name 
                FROM asset a 
                LEFT JOIN category c ON a.category_id = c.id 
                WHERE a.id = :id
            ");
            $stmt->execute([':id' => $id]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
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

            $allowedFields = ['name', 'category_id', 'department', 'status', 'purchase_date', 'warranty_expiry', 'value', 'usage_type'];
            $fieldsToUpdate = [];
            $params = [':id' => $id];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    if ($field === 'category_id') {
                        $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :category_id");
                        $stmt->execute([':category_id' => $input[$field]]);
                        if (!$stmt->fetch()) {
                            http_response_code(400);
                            echo json_encode(['error' => 'Invalid category ID']);
                            break 2;
                        }
                    }
                    $fieldsToUpdate[] = "$field = :$field";
                    $params[":$field"] = $input[$field];
                }
            }

            if (empty($fieldsToUpdate)) {
                http_response_code(400);
                echo json_encode(['error' => 'No valid fields to update']);
                break;
            }

            $sql = "UPDATE asset SET " . implode(', ', $fieldsToUpdate) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $stmt = $pdo->prepare("
                SELECT a.*, c.name AS category_name 
                FROM asset a 
                LEFT JOIN category c ON a.category_id = c.id 
                WHERE a.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $updated = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($updated) {
                echo json_encode($updated);
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

            $stmt = $pdo->prepare("SELECT id FROM asset WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Asset not found']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM asset WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['message' => 'Asset deleted successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
