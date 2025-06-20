<?php
// ✅ Always include CORS first
require __DIR__ . '/config/cors.php';

// ✅ Clean and parse route
$request = isset($_GET['route']) ? '/' . trim($_GET['route'], '/') : '/';
$method = $_SERVER['REQUEST_METHOD'];

// ✅ Log for debugging (optional, good for production trace)
error_log("Route: $request");
error_log("Method: $method");

// ✅ Assets: /api/assets or /api/assets/{id}
if (preg_match('#^/api/assets(?:/(\d+))?$#', $request, $matches)) {
    if (!empty($matches[1])) {
        $_GET['id'] = $matches[1]; // Set asset ID
    }
    require __DIR__ . '/api/assets.php';
    exit;
}

// ✅ Assets department PATCH: /api/assets/{id}/department
if (preg_match('#^/api/assets/(\d+)/department$#', $request, $matches)) {
    $_GET['id'] = $matches[1];
    $_GET['department_endpoint'] = true;
    require __DIR__ . '/api/assets.php';
    exit;
}

// ✅ Categories: /api/categories
if ($request === '/api/categories') {
    require __DIR__ . '/api/categories.php';
    exit;
}

// ✅ Departments: /api/departments
if ($request === '/api/departments') {
    require __DIR__ . '/api/department.php';
    exit;
}

// ❌ No match = 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'error' => 'Endpoint not found',
    'requested' => $request,
    'method' => $method,
    'uri' => $_SERVER['REQUEST_URI'],
    'path_info' => $_SERVER['PATH_INFO'] ?? null
]);
