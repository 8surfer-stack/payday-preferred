# Payday Preferred

I am building Payday Preferred for CSC 640 under Option 1. My professor approved my project on November 3, 2026.

My app will record income, split it using customizable percentages, and save simulated-transfer history. I will use PHP/MySQL for Stage 1 and Laravel/MySQL for Stage 2, with the same acceptance tests for both. I'll simulate transfers without moving real money.

## Part 1 files
- [Stage 1 plan](plan/HW4_plan_stage1.md)
- [Part 1 checklist](plan/HW4_part1_checklist.md)
- [Acceptance contract](acceptance.md)
- [Executable acceptance tests](tests/acceptance.py)
- [Verification status](tests/results/stage1.md)
- [Canvas progress text](plan/canvas-progress.md)

## How I will check my Mac setup
I'll install and open Docker Desktop for Mac, then open a terminal in this project folder. This setup runs PHP and MySQL in containers, so I do not need separate PHP/MySQL installations on my Mac.

```bash
docker --version
docker compose version
docker compose config
docker compose up --build -d
curl --fail http://localhost:8080/health
```

I expect the health response to show Payday Preferred, a PHP version, and database_connected set to true. I can open http://localhost:8080/ to see the same response. If it fails, I'll run `docker compose logs app db`. I need port 8080 to be available and will let the first startup finish before testing.

This package uses PHP 8.3 with Apache and PDO MySQL, MySQL 8.4, and Python 3.12 for tests. Apache is the chosen Option 1 local web server; the NGINX-specific requirement in Part 1 is explicitly stated for Option 2. I'll record the actual versions and image digests after startup. The example passwords are disposable local test credentials. Only localhost:8080 is exposed. I'll use this configuration locally.

I can stop the containers and keep my test database with:
```bash
docker compose down
```

## Run acceptance tests
```bash
docker compose --profile test run --rm tests
```
My app now connects to MySQL and saves valid income entries.
I manually checked that zero, negative, text, missing, and fractional
amounts return HTTP 422 without adding database rows. I've recorded
those results in tests/results/stage1.md.

I added code/stage1/schema.sql and connected it to the MySQL startup
configuration. Automatic table creation on a fresh database hasn't
been verified yet.

I still need to implement allocation rules, distribution previews,
simulated transfers, and transaction history. I haven't run the full
automated acceptance suite yet. The manual income checks don't mean
the full suite passes.

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
