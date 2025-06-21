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
    // ✅ FIX: Use correct variable names from db.php config
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $method = $_SERVER['REQUEST_METHOD'];
    
    // ✅ FIX: Get ID from $_GET (set by route.php) instead of parsing URL
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get a single category by ID
                $stmt = $pdo->prepare("SELECT * FROM category WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($category) {
                    echo json_encode($category);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Category not found']);
                }
            } else {
                // Get all categories
                $stmt = $pdo->query("SELECT * FROM category ORDER BY name");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($categories);
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

            if (!isset($input['name']) || empty(trim($input['name']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Category name is required']);
                break;
            }

            // Check if category name already exists
            $stmt = $pdo->prepare("SELECT id FROM category WHERE name = :name");
            $stmt->execute([':name' => trim($input['name'])]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Category name already exists']);
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO category (name, description) VALUES (:name, :description)");
            $stmt->execute([
                ':name' => trim($input['name']),
                ':description' => $input['description'] ?? null
            ]);

            $newId = $pdo->lastInsertId();
            
            // Return the created category
            $stmt = $pdo->prepare("SELECT * FROM category WHERE id = :id");
            $stmt->execute([':id' => $newId]);
            $newCategory = $stmt->fetch(PDO::FETCH_ASSOC);

            http_response_code(201);
            echo json_encode($newCategory);
            break;

        case 'PUT':
        case 'PATCH':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Category ID required']);
                break;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                break;
            }

            if (!isset($input['name']) || empty(trim($input['name']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Category name is required']);
                break;
            }

            // Check if category exists
            $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
                break;
            }

            // Check if new name conflicts with existing category (exclude current one)
            $stmt = $pdo->prepare("SELECT id FROM category WHERE name = :name AND id != :id");
            $stmt->execute([':name' => trim($input['name']), ':id' => $id]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Category name already exists']);
                break;
            }

            $stmt = $pdo->prepare("UPDATE category SET name = :name, description = :description WHERE id = :id");
            $stmt->execute([
                ':name' => trim($input['name']),
                ':description' => $input['description'] ?? null,
                ':id' => $id
            ]);

            // Return updated category
            $stmt = $pdo->prepare("SELECT * FROM category WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $updatedCategory = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode($updatedCategory);
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Category ID required']);
                break;
            }

            // Check if category exists
            $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
                break;
            }

            // Check if category is being used by any assets
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM asset WHERE category_id = :id");
            $stmt->execute([':id' => $id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Cannot delete category',
                    'message' => "Category is used by $count asset(s)"
                ]);
                break;
            }

            // Delete the category
            $stmt = $pdo->prepare("DELETE FROM category WHERE id = :id");
            $stmt->execute([':id' => $id]);

            echo json_encode(['message' => 'Category deleted successfully']);
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
