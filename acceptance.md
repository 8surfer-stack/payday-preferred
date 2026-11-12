# Payday Preferred: Shared Acceptance Contract

I will use these acceptance checks for Stage 1 (PHP/MySQL) and Stage 2 (Laravel/MySQL). My professor approved my project on November 3, 2026. I still need to finish some interface details. The route names and HTTP statuses below are my proposed design choices. I have not run the application tests yet.

## Data and proposed interface
I will use a disposable test database with sample data. The test fixture starts empty; the runner creates the 50% checking / 30% savings / 20% investment rule set when needed, with fixed ordering. Currency is USD, stored as integer cents. Rule percentages accept whole percentages from 0 to 100 and must total exactly 100. Each destination has a unique nonempty label. These are the limits I am proposing for my first version.

Proposed JSON interface (same routes and response behavior in both stages):
- POST /api/incomes: amount_cents and description; returns 201 with id and amount_cents.
- PUT /api/allocation-rules: destinations array with label and percentage; returns 200 with saved destinations.
- POST /api/distributions/preview: income_id; returns 200 with destination amounts and total_cents; writes no transfers.
- POST /api/transfers/simulate: income_id; returns 201 with batch_id and entries; persists one complete batch only.
- GET /api/transactions: accepts optional limit (integer 1–100, default 100), returns 200 with batches, including income_id and entries. Invalid limit returns 422. This parameter is a proposed interface choice to provide a reproducible failure case.
- GET /api/transactions/{batch_id}: returns 200 with one batch, or 404 with an error code.

Errors return JSON with an error code. Invalid inputs return 422; unknown records return 404; repeated simulation for an already processed income returns 409. None of these failures may change financial records. No real transfers occur.

## Cases
| ID | Workflow / input | Expected status and content |
|---|---|---|
| I1 | Record amount_cents=100000, description=Test paycheck | 201; id present; amount_cents exactly 100000; one income saved |
| I2 | Record amount_cents=0 or -100 | 422; error code present; no income saved; run each input |
| I3 | Missing amount_cents, nonnumeric value, or fractional cents | 422; error code present; no income saved; run each input |
| R1 | Save checking=50, savings=30, investment=20 | 200; all three labels and percentages returned and persisted |
| R2 | Save percentages totaling 90 or 110 | 422; error code; previously valid rules unchanged; run both |
| R3 | Negative, over-100, fractional, or nonnumeric percentage | 422; error code; valid rules unchanged; run each |
| R4 | Empty rules, blank destination, or duplicate destination label | 422; error code; valid rules unchanged; run each |
| C1 | Preview I1 using R1 | 200; amounts 50000, 30000, 20000; total_cents=100000; no transfers created |
| C2 | Preview amount_cents=10001 using R1 | 200; amounts 5001, 3000, 2000; sum=10001; no transfers created |
| C3 | Preview nonexistent income_id | 404; error code; no transfers created |
| C4 | Preview existing income with no allocation rules | 422; error code; no transfers created |
| T1 | Simulate I1 using R1 | 201; batch_id present; three entries of 50000,30000,20000; persisted total=100000; explicitly simulated |
| T2 | Simulate same income again | 409; error code; no second batch or entries |
| T3 | Simulate nonexistent income | 404; error code; no new batch or entries |
| T4 | Simulate existing income with no rules | 422; error code; no partial batch or entries |
| H1 | List history after T1 | 200; exactly one batch with matching income_id, destinations and amounts |
| H2 | Read T1 batch by batch_id | 200; same complete record; remains available after application restart |
| H3 | List history on reset database | 200; empty batches array |
| H4 | Read nonexistent batch_id | 404; error code |
| H5 | List history with limit=0, limit=101 or limit=abc | 422; error code; history unchanged; run each input |
| C5 | Preview one-cent income with a 50/50 rule set | 200; first destination=1 cent, second=0; total_cents=1 |

Rounding: use largest remainders after flooring allocations to whole cents; break ties by saved destination order. This preserves the full income amount. I will include a tie test with a one-cent income and a 50/50 split: the first destination receives one cent and the second zero.

Authentication is not specified in the approved HW1 proposal; project approval alone does not establish the detailed authentication design. If I protect any endpoint, I will expand these checks with valid-token success plus missing-token and invalid-token rejection, expected status/content, and unchanged-state checks for each protected endpoint.

## Executable acceptance tests

My executable test runner: [tests/acceptance.py](tests/acceptance.py), using Python standard-library unittest and urllib.request. The runner asserts HTTP statuses and JSON content, tests invalid input, exact allocations and rounding, failed-rule preservation, duplicate-transfer rejection, and matching saved history. It exits nonzero on failure.

I will run: `docker compose --profile test run --rm tests`

I will use the dedicated payday_test database. Before each full test run, I will clear the application rows and allocation rules with the Stage 1 reset script once I have implemented it. The suite starts with empty rules/history, creates its own incomes and rules, and runs test_01 through test_06 in order. I will keep these tests separate from personal data and existing demo data. There is no public reset endpoint. Stage 1 business endpoints and its schema/reset script are still implementation work; this Part 1 package supplies the PHP/MySQL environment probe and executable contract tests in advance.

Contract details for implementation: successful previews return entries (ordered objects with label and amount_cents) plus total_cents. Simulation additionally returns integer batch_id and income_id and simulated=true. History returns batches containing those same complete objects. IDs are positive integers. All error responses include a nonempty string error. The monetary baseline uses positive integer cents up to 100000000; I will add upper-bound checks in the implementation and tests before expanding the scope. Unexpected malformed JSON must return 400 with an error string. API routes are a proposed local single-user baseline; if I add protection, I will add valid, missing and invalid token cases for every protected route.

I still need an executable check that saved data survives an application restart. I will record that separately from the history-read check. Once the schema exists, I will also verify that failed income requests create no rows.

I will run the same tests against both stages, changing only the base URL and documented data setup. I will fix failures without weakening the tests.

## Results
See [tests/results/stage1.md](tests/results/stage1.md). I have not verified a passing application test. ChatGPT checked the Python syntax successfully, but could not run PHP, MySQL or Docker in its workspace. Full acceptance tests are expected to fail against the environment probe because business endpoints are not implemented yet.
