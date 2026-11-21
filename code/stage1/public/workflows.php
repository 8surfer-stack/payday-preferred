<?php

function requestData(): array
{
    try {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        respond(400, ['error' => 'invalid_json']);
    }

    if (!is_array($data)) {
        respond(422, ['error' => 'invalid_input']);
    }

    return $data;
}

function saveRules(PDO $pdo, array $data): void
{
    $destinations = $data['destinations'] ?? null;
    if (!is_array($destinations) || !array_is_list($destinations) || !$destinations) {
        respond(422, ['error' => 'invalid_destinations']);
    }

    $labels = [];
    $rules = [];
    $total = 0;
    foreach ($destinations as $destination) {
        $label = $destination['label'] ?? null;
        $percentage = $destination['percentage'] ?? null;
        if (!is_string($label) || trim($label) === '' || isset($labels[$label])
            || !is_int($percentage) || $percentage < 0 || $percentage > 100) {
            respond(422, ['error' => 'invalid_destination']);
        }

        $labels[$label] = true;
        $rules[] = ['label' => $label, 'percentage' => $percentage];
        $total += $percentage;
    }

    if ($total !== 100) {
        respond(422, ['error' => 'invalid_percentage_total']);
    }

    $encoded = json_encode($rules, JSON_THROW_ON_ERROR);
    $statement = $pdo->prepare(
        'INSERT INTO allocation_rules (id, destinations) VALUES (1, ?)
         ON DUPLICATE KEY UPDATE destinations = ?'
    );
    $statement->execute([$encoded, $encoded]);
    respond(200, ['destinations' => $rules]);
}

function calculateEntries(int $total, array $rules): array
{
    $entries = [];
    $remainders = [];
    $remaining = $total;
    foreach ($rules as $index => $rule) {
        $product = $total * $rule['percentage'];
        $amount = intdiv($product, 100);
        $entries[] = ['label' => $rule['label'], 'amount_cents' => $amount];
        $remainders[$index] = $product % 100;
        $remaining -= $amount;
    }

    // A stable sort keeps the saved destination order when remainders tie.
    arsort($remainders);
    foreach (array_slice(array_keys($remainders), 0, $remaining) as $index) {
        $entries[$index]['amount_cents']++;
    }

    return $entries;
}

function transferBatch(array $row): array
{
    return [
        'batch_id' => (int) $row['id'],
        'income_id' => (int) $row['income_id'],
        'total_cents' => (int) $row['total_cents'],
        'entries' => json_decode($row['entries'], true, 512, JSON_THROW_ON_ERROR),
        'simulated' => true,
    ];
}

function distribute(PDO $pdo, array $data, bool $simulate): void
{
    $incomeId = $data['income_id'] ?? null;
    if (!is_int($incomeId) || $incomeId < 1) {
        respond(422, ['error' => 'invalid_income_id']);
    }

    $income = $pdo->prepare('SELECT amount_cents FROM incomes WHERE id = ?');
    $income->execute([$incomeId]);
    $amount = $income->fetchColumn();
    if ($amount === false) {
        respond(404, ['error' => 'income_not_found']);
    }

    $total = (int) $amount;
    $encodedRules = $pdo->query('SELECT destinations FROM allocation_rules WHERE id = 1')->fetchColumn();
    if ($encodedRules === false) {
        respond(422, ['error' => 'allocation_rules_required']);
    }

    $entries = calculateEntries($total, json_decode($encodedRules, true, 512, JSON_THROW_ON_ERROR));
    if (!$simulate) {
        respond(200, ['entries' => $entries, 'total_cents' => $total]);
    }

    // One row stores the entire batch. The unique income ID prevents duplicate transfers.
    $encodedEntries = json_encode($entries, JSON_THROW_ON_ERROR);
    $insert = $pdo->prepare('INSERT INTO transfer_batches (income_id, total_cents, entries) VALUES (?, ?, ?)');
    try {
        $insert->execute([$incomeId, $total, $encodedEntries]);
    } catch (PDOException $exception) {
        if (($exception->errorInfo[1] ?? null) === 1062) {
            respond(409, ['error' => 'transfer_already_simulated']);
        }
        throw $exception;
    }

    respond(201, transferBatch([
        'id' => $pdo->lastInsertId(),
        'income_id' => $incomeId,
        'total_cents' => $total,
        'entries' => $encodedEntries,
    ]));
}

function listTransfers(PDO $pdo, array $query): void
{
    $limit = filter_var($query['limit'] ?? '100', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 100],
    ]);
    if ($limit === false) {
        respond(422, ['error' => 'invalid_limit']);
    }

    $statement = $pdo->prepare('SELECT * FROM transfer_batches ORDER BY id DESC LIMIT ?');
    $statement->bindValue(1, $limit, PDO::PARAM_INT);
    $statement->execute();
    respond(200, ['batches' => array_map('transferBatch', $statement->fetchAll(PDO::FETCH_ASSOC))]);
}
