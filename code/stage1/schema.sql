-- My Stage 1 income table for Payday Preferred.
-- I store amounts in whole cents.

CREATE TABLE IF NOT EXISTS incomes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amount_cents INT NOT NULL,
    description VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (amount_cents BETWEEN 1 AND 100000000)
);