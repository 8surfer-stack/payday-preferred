<?php
header('Content-Type: application/json');

// Send a JSON response and stop processing.
function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

require __DIR__ . '/workflows.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Connect to MySQL.
try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST')
        . ';dbname=' . getenv('DB_NAME')
        . ';charset=utf8mb4',
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    respond(503, ['error' => 'database_unavailable']);
}

// Keep our existing health check.
if ($method === 'GET' && ($path === '/' || $path === '/health')) {
    $connected = (int) $pdo->query('SELECT 1')->fetchColumn() === 1;

    respond(200, [
        'project' => 'Payday Preferred',
        'php' => PHP_VERSION,
        'database_connected' => $connected
    ]);
}

// Accept a new income entry.
if ($path === '/api/incomes' && $method === 'POST') {
    $data = requestData();

    $amount = $data['amount_cents'] ?? null;
    $description = $data['description'] ?? '';

    if (!is_int($amount) || $amount < 1 || $amount > 100000000) {
        respond(422, ['error' => 'invalid_amount']);
    }

    if (!is_string($description) || strlen($description) > 255) {
        respond(422, ['error' => 'invalid_description']);
    }

    try {
        $statement = $pdo->prepare(
            'INSERT INTO incomes (amount_cents, description)
             VALUES (:amount, :description)'
        );

        $statement->execute([
            'amount' => $amount,
            'description' => $description
        ]);

        respond(201, [
            'id' => (int) $pdo->lastInsertId(),
            'amount_cents' => $amount,
            'description' => $description
        ]);
    } catch (PDOException $e) {
        respond(500, ['error' => 'income_save_failed']);
    }
}

try {
    if ($method === 'PUT' && $path === '/api/allocation-rules') {
        saveRules($pdo, requestData());
    }

    if ($method === 'POST' && $path === '/api/distributions/preview') {
        distribute($pdo, requestData(), false);
    }

    if ($method === 'POST' && $path === '/api/transfers/simulate') {
        $token = getenv('PAYDAY_SIMULATION_TOKEN');
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $authorization = $headers['authorization'] ?? '';
        if ($token === false || $token === '' || !hash_equals('Bearer ' . $token, $authorization)) {
            header('WWW-Authenticate: Bearer');
            respond(401, ['error' => 'unauthorized']);
        }
        distribute($pdo, requestData(), true);
    }

    if ($method === 'GET' && $path === '/api/transactions') {
        listTransfers($pdo, $_GET);
    }

    if ($method === 'GET' && preg_match('~^/api/transactions/([0-9]+)$~', $path, $matches)) {
        $statement = $pdo->prepare('SELECT * FROM transfer_batches WHERE id = ?');
        $statement->execute([$matches[1]]);
        $batch = $statement->fetch(PDO::FETCH_ASSOC);
        if ($batch === false) {
            respond(404, ['error' => 'batch_not_found']);
        }
        respond(200, transferBatch($batch));
    }
} catch (PDOException $exception) {
    respond(500, ['error' => 'database_operation_failed']);
}

respond(404, ['error' => 'not_found']);
