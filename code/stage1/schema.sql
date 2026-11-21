-- My Stage 1 income table for Payday Preferred.
-- I store amounts in whole cents.

CREATE TABLE IF NOT EXISTS incomes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amount_cents INT NOT NULL,
    description VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (amount_cents BETWEEN 1 AND 100000000)
);

CREATE TABLE IF NOT EXISTS allocation_rules (
    id INT PRIMARY KEY,
    destinations JSON NOT NULL
);

CREATE TABLE IF NOT EXISTS transfer_batches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    income_id INT UNSIGNED NOT NULL UNIQUE,
    total_cents INT NOT NULL,
    entries JSON NOT NULL,
    FOREIGN KEY (income_id) REFERENCES incomes(id)
);
