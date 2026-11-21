---
marp: true
---

# Payday Preferred
## Stage 1 API tutorial

Run `docker compose up --build -d` from the project root.
Open `http://localhost:8080/health` to check MySQL connectivity.
Open `http://localhost:8080/test.html` to try each endpoint through NGINX.

Requests with bodies use JSON. Amounts are whole USD cents. This local Option 1 API has no authentication. Transfers are simulated.

---

## Record income and set allocation rules

**POST /api/incomes**  
Send `{"amount_cents":100000,"description":"Test paycheck"}`.  
HTTP 201 returns the income `id`, `amount_cents`, and `description`.  
Sending `{"amount_cents":0}` returns 422 and `{"error":"invalid_amount"}`.

**PUT /api/allocation-rules**  
Send `{"destinations":[{"label":"checking","percentage":100}]}`.  
HTTP 200 returns the saved `destinations`.  
A total of 90 returns 422 and `{"error":"invalid_percentage_total"}`.

---

## Preview and simulate a transfer

**POST /api/distributions/preview**  
Send `{"income_id":1}` using a saved income ID.  
HTTP 200 returns `entries` with labels and cent amounts plus `total_cents`.  
An unknown ID returns 404 and `{"error":"income_not_found"}`.

**POST /api/transfers/simulate**  
Send the same income ID. HTTP 201 saves one batch and returns its `batch_id`, `income_id`, `total_cents`, `entries`, and `simulated: true`.  
Retrying that income returns 409 and `{"error":"transfer_already_simulated"}`.

---

## Read transfer history

**GET /api/transactions?limit=10**  
HTTP 200 returns `batches`, newest first. An empty history returns `{"batches":[]}`.  
A limit of 0 returns 422 and `{"error":"invalid_limit"}`.

**GET /api/transactions/1**  
HTTP 200 returns the saved batch for ID 1, if present.  
An unknown batch returns 404 and `{"error":"batch_not_found"}`.

See the README for clean-database acceptance-test instructions and saved results.
