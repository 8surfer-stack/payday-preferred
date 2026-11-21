# Payday Preferred

I am building Payday Preferred for CSC 640 under Option 1. My professor approved my project on November 3, 2026.

Stage 1 records income, splits it using customizable percentages, and saves simulated-transfer history. I will use PHP/MySQL for Stage 1 and Laravel/MySQL for Stage 2, with the same acceptance tests for both. I'll simulate transfers without moving real money.

## HW4 files
- [Stage 2 plan](plan/HW4_plan_stage2.md)
- [Stage 1 API tutorial](presentation/stage1-tutorial.md) ([PDF](presentation/stage1-tutorial.pdf))
- [Stage 1 plan](plan/HW4_plan_stage1.md)
- [Part 1 checklist](plan/HW4_part1_checklist.md)
- [Acceptance contract](acceptance.md)
- [Executable acceptance tests](tests/acceptance.py)
- [Verification status](tests/results/stage1.md)
- [Canvas progress text](plan/canvas-progress.md)

## Run Stage 1
I'll install and open Docker Desktop for Mac, then open a terminal in this project folder. This setup runs PHP and MySQL in containers, so I do not need separate PHP/MySQL installations on my Mac.

```bash
docker --version
docker compose version
docker compose config
docker compose up --build -d
curl --fail http://localhost:8080/health
```

I expect the health response to show Payday Preferred, a PHP version, and database_connected set to true. I can open http://localhost:8080/ to see the same response. If it fails, I'll run `docker compose logs app db`. I need port 8080 to be available and will let the first startup finish before testing.

NGINX listens on localhost:8080 and forwards to the PHP 8.3 Apache container. MySQL 8.4 stores the project data. The example passwords are disposable local test credentials.

Open `http://localhost:8080/test.html` to send requests to all six routes. Use the income ID returned when you create a paycheck, and the batch ID returned from a simulation.

Fresh databases apply the schema on startup. If your Docker volume predates this branch, apply the same schema once to create the missing tables without deleting income records:

```bash
docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < code/stage1/schema.sql
```

I can stop the containers and keep my test database with:
```bash
docker compose down
```

## Run acceptance tests

Use a fresh, dedicated Compose project so the runner starts with empty history and allocation rules. Stop any project already listening on port 8080 first.

```bash
docker compose -p payday-stage1-check up --build -d
curl --fail http://localhost:8080/health
docker compose -p payday-stage1-check --profile test run --rm tests
```

If the health check is still starting, retry it before running the suite. Save the observed pass/fail output in [the Stage 1 results](tests/results/stage1.md). Run repeat checks with a fresh, uniquely named Compose project. You can remove the disposable project and its test data after recording results with `docker compose -p payday-stage1-check down --volumes`.

## Reviewer version

Use the HW4 Part 2 review branch and record the exact commit with `git rev-parse HEAD`. The saved results document startup, MySQL connectivity, and manual income checks from the earlier implementation. The full automated suite for this branch is pending.

## Course workflow
I need to submit these three individual files for Part 1: plan/HW4_part1_checklist.md, plan/HW4_plan_stage1.md and acceptance.md. I'll keep README at the project root, copy my progress update to my HW1 Canvas page, and add that page’s URL to my plan. I'll confirm submission availability and credit with my professor.

The supplied tentative schedule lists Part 2 on November 21 and Part 3/HW5 on December 10. I'll confirm the times on Canvas. Part 2 submits stage1_evaluation.md and HW4_plan_stage2.md and updates the Canvas plan. Part 3 submits stage2_evaluation.md and records actual dates in the existing Stage 2 plan.

After each stage deadline, I will review my three assigned peers, run their setup and tests, check one README or tutorial claim, and submit peer-review.md. I'll give each peer a score out of 100 and discuss strengths, weaknesses, progress and product quality. The schedule says reviews are due two days after submission; I'll check Canvas. HW4 instructions state missing peer review results in zero for that Part.

Both runnable stages belong in code/stage1 and code/stage2 (Stage 2 to be created), presentation/ holds Marp material, and plan/ tracks planned/actual dates. I will write a half-page comparison.md in Stage 2 about automation, manual work, benefits and costs, with links to my test results. I will link it from HW5. Docker, GitHub Actions, Hugo and Marp are planned automation/documentation tools; Actions/Hugo are not implemented here.

## References and AI assistance
- Course assignment: https://nku.instructure.com/courses/92884/assignments/1628198
- Supplied HW1 rubric, HW4.md, HW4_part1.md, checklist, plan template, Fall 2026 schedule and syllabus.
- Docker Compose: https://docs.docker.com/compose/gettingstarted/
- PHP PDO: https://www.php.net/manual/en/book.pdo.php
- Docker Desktop Mac setup: https://docs.docker.com/desktop/setup/install/mac-install/
- Documentation repository: https://github.com/8surfer-stack/csc640-documentation

I used ChatGPT to help draft these documents, the setup files, and the executable tests. I still need to review the code and make sure I can explain it. I'll cite the lecture files and page numbers when I use course material. I've verified the startup check and manual income tests on my Mac. The full automated acceptance suite is still unverified.
