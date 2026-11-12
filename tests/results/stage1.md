# My Stage 1 verification results

## Startup and database connection

I ran `docker compose up --build -d` on my Mac.
The app container started, and MySQL reported healthy.

After moving my project folder, I ran:
`docker compose up -d --force-recreate app`

I opened http://localhost:8080/health and got:

{"project":"Payday Preferred","php":"8.3.36","database_connected":true}

Result: PASS. The app runs and connects to MySQL.

## Income entry checks

I tested POST /api/incomes with curl in the VS Code terminal.
These were manual checks of the HTTP status and response body.

| Input | Expected result | Actual result | Status |
| --- | --- | --- | --- |
| 100000 cents ($1,000), description "Test paycheck" | HTTP 201 with the saved income | HTTP 201, id 1, amount_cents 100000, description "Test paycheck" | PASS |
| 0 cents | HTTP 422, invalid_amount | Matched expected result | PASS |
| -100 cents | HTTP 422, invalid_amount | Matched expected result | PASS |
| Text: "hello" | HTTP 422, invalid_amount | Matched expected result | PASS |
| Missing amount_cents | HTTP 422, invalid_amount | Matched expected result | PASS |
| 10.5 cents | HTTP 422, invalid_amount | Matched expected result | PASS |

For each request, I used:

curl -i http://localhost:8080/api/incomes \
  -H 'Content-Type: application/json' \
  -d '<JSON body>'

The JSON bodies were:

- {"amount_cents":100000,"description":"Test paycheck"}
- {"amount_cents":0,"description":"Invalid zero paycheck"}
- {"amount_cents":-100,"description":"Negative amount test"}
- {"amount_cents":"hello","description":"Text amount test"}
- {"description":"Missing amount test"}
- {"amount_cents":10.5,"description":"Fractional cents test"}

## Database check after invalid requests

I ran:

docker compose exec db mysql -u payday -p payday_test -e "SELECT id, amount_cents, description FROM incomes;"

Expected: Only the valid paycheck should be saved.

Actual: One row remained:
- id: 1
- amount_cents: 100000
- description: Test paycheck

Result: PASS. None of the invalid requests added a row.

## What still needs testing

These results cover startup and the income requests listed above.
They don't mean the full Stage 1 acceptance suite passes.

I still need to implement and test allocation rules, distribution
previews, simulated transfers, and transaction history.

I'll also save the database setup as a project file so I can
recreate the table without typing the SQL manually.

ChatGPT previously checked the Python syntax with:
`python3 -m py_compile tests/acceptance.py`

That check passed, but it doesn't count as an application test.
I haven't run the full automated acceptance suite yet.
