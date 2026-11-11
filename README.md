# Payday Preferred

CSC 640 Option 1 project by Barnabas Olatoyan. Professor approval confirmed by the student November 10, 2026.

The approved proposal records income, allocates customizable percentages to destination labels, and stores simulated-transfer history. Stage 1 uses PHP/MySQL; Stage 2 will use Laravel/MySQL and the same acceptance tests. Transfers are simulated.

## Part 1 files
- [Stage 1 plan](plan/HW4_plan_stage1.md)
- [Part 1 checklist](plan/HW4_part1_checklist.md)
- [Acceptance contract](acceptance.md)
- [Executable acceptance tests](tests/acceptance.py)
- [Verification status](tests/results/stage1.md)
- [Canvas progress text](plan/canvas-progress.md)

## Verify the environment on your Mac
Install and open Docker Desktop for Mac, then open a terminal in this repository. PHP and MySQL run inside containers; separate Mac PHP/MySQL installations are not required for this route.

```bash
docker --version
docker compose version
docker compose config
docker compose up --build -d
curl --fail http://localhost:8080/health
```

Expected health response: project is Payday Preferred, a PHP version is present, and database_connected is true. Open http://localhost:8080/ in your browser to see the same response. If it fails, run `docker compose logs app db`. Port 8080 must be free. First startup downloads images and initializes MySQL; wait for startup to finish before testing.

This package uses PHP 8.3 with Apache and PDO MySQL, MySQL 8.4, and Python 3.12 for tests. Apache is the chosen Option 1 local web server; the NGINX-specific requirement in Part 1 is explicitly stated for Option 2. Record actual versions/image digests after startup. The example passwords are disposable local test credentials. Only localhost:8080 is exposed; do not deploy this configuration publicly.

Stop while preserving the test database:
```bash
docker compose down
```

## Run acceptance tests
```bash
docker compose --profile test run --rm tests
```
The current PHP file is an environment probe, not the completed budgeting application. The executable tests are expected to fail until Stage 1 business endpoints and the schema/reset fixture are implemented. See acceptance.md for fixtures and expected output. A passing health check only verifies PHP-to-MySQL connectivity.

## Course workflow
Submit these three individual files for Part 1: plan/HW4_part1_checklist.md, plan/HW4_plan_stage1.md and acceptance.md. Keep README at the repository root. Copy the progress text into your existing HW1 Canvas progress page and add its URL to the plan. Submission availability and credit must be confirmed with the professor.

The supplied tentative schedule lists Part 2 on November 21 and Part 3/HW5 on December 10. Confirm Canvas times. Part 2 submits stage1_evaluation.md and HW4_plan_stage2.md and updates the Canvas plan. Part 3 submits stage2_evaluation.md and records actual dates in the existing Stage 2 plan.

After each stage deadline, review all three assigned peers, run their setup/tests, verify one README/tutorial claim, and submit peer-review.md with scores out of 100, strengths, weaknesses, progress and product quality. The schedule says reviews are due two days after submission; check Canvas. HW4 instructions state missing peer review results in zero for that Part.

Both runnable stages belong in code/stage1 and code/stage2 (Stage 2 to be created), presentation/ holds Marp material, and plan/ tracks planned/actual dates. Write a half-page comparison.md in Stage 2 covering automation, manual work, benefits/costs and test-result links; link it from HW5. Docker, GitHub Actions, Hugo and Marp are planned automation/documentation tools; Actions/Hugo are not implemented here.

## References and AI assistance
- Course assignment: https://nku.instructure.com/courses/92884/assignments/1628198
- Supplied HW1 rubric, HW4.md, HW4_part1.md, checklist, plan template, Fall 2026 schedule and syllabus.
- Docker Compose: https://docs.docker.com/compose/gettingstarted/
- PHP PDO: https://www.php.net/manual/en/book.pdo.php
- Docker Desktop Mac setup: https://docs.docker.com/desktop/setup/install/mac-install/
- Documentation repository: https://github.com/8surfer-stack/csc640-documentation

ChatGPT drafted these documents, the environment probe/configuration, and executable tests. Barnabas must review and be able to explain them. Add precise lecture file/page citations when course material is reused. No application acceptance test has been reported as passed.
