# HW4 Stage 2 Plan

**Project:** Payday Preferred (Option 1)  
**Part 3 deadline:** December 10, 2026, per the tentative course schedule. Confirm the time on Canvas.

## Part 3 submission

| Planned | Actual | Comment |
|---|---|---|
| December 10 | TBD | Submit the Stage 2 evaluation and update this plan with actual dates and links to evidence. |

## November 21 Laravel/MySQL trial

I installed Laravel Framework 12.73.0 on my Mac and ran it in Docker using the separate `payday-stage2-trial` Compose project. The first migration attempt used Laravel's default database name and username and MySQL denied the connection. I set the connection to the trial project's MySQL service (`db`), database (`payday_test`), and application user (`payday`), then cleared the config cache and reran `php artisan migrate --force` and `php artisan migrate:status`.

**Result:** MySQL recorded the users, cache, and jobs migrations as **Ran** in batch 1. This verifies the Laravel/MySQL connection and migrations. The Laravel health endpoint through NGINX is still pending, so Milestone 1 stays open.

## Milestones

| What I plan to complete | Planned | Actual | Comment |
|---|---|---|---|
| Try the approved Laravel/MySQL setup and verify a health endpoint through NGINX | Nov 24 | TBD | Record setup commands and a reproducible startup result. |
| Port the Stage 1 API contract to Laravel and verify it against MySQL | Dec 2 | TBD | Preserve status codes, rounding, duplicate-transfer handling, and history. |
| Run the shared acceptance tests on a clean database and publish evidence | Dec 7 | TBD | Save results and update the tutorial, README, and Stage 1 versus Stage 2 comparison. |
