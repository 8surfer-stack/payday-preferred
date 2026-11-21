# My Stage 1 verification results

## Earlier manual checks

### Startup and database connection

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

## November 21, 2026 · Clean Stage 1 run on my Mac

I checked out `hw4-part2` and ran the suite in the separate `payday-stage1-check` Compose project. Docker created a new database volume, reported MySQL healthy, and started the app and NGINX. The health response through port 8080 reported PHP 8.3.37 and `database_connected: true`.

Tested branch revision inferred from the checkout before this documentation update: [705a484](https://github.com/8surfer-stack/payday-preferred/tree/705a48458a6b56617adec8f767e8722d74b64168). My screenshot shows the branch tracking its remote. It does not show a separate commit hash command.

```bash
docker compose -p payday-stage1-check up --build -d
curl --fail http://localhost:8080/health
docker compose -p payday-stage1-check --profile test run --rm tests
```

```text
Ran 6 tests in 0.126s

OK
```

All six automated tests passed through NGINX and MySQL. A separate restart persistence check and a browser walkthrough of the HTML test page are still pending.
