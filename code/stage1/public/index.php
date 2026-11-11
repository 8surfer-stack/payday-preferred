<?php
// Part 1 environment probe. Business endpoints are Stage 1 implementation work.
header('Content-Type: application/json');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/' && $path !== '/health') {
    http_response_code(404);
    echo json_encode(['error' => 'not_implemented']);
    exit;
}
try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4',
        getenv('DB_USER'), getenv('DB_PASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $ok = (int)$pdo->query('SELECT 1')->fetchColumn() === 1;
    echo json_encode(['project' => 'Payday Preferred', 'php' => PHP_VERSION, 'database_connected' => $ok]);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['error' => 'database_unavailable']);
}
