# Payday Preferred progress:

I chose Option 1, and my professor approved my project on November 3, 2026.
Project repository: https://github.com/8surfer-stack/payday-preferred
Stage 1 plan: https://github.com/8surfer-stack/payday-preferred/blob/main/plan/HW4_plan_stage1.md
Acceptance criteria: https://github.com/8surfer-stack/payday-preferred/blob/main/acceptance.md

I've prepared my Stage 1 plan, README, acceptance criteria, and executable
test runner. I verified Docker and the PHP/MySQL connection
on my Mac.

Income entry now saves valid amounts. My manual checks confirmed that
zero, negative, text, missing, and fractional amounts are rejected without
adding database rows. I've recorded those results in tests/results/stage1.md.

I've added the income table schema and connected it to Docker's database
initialization. I still need to verify a fresh setup, finish allocation
rules, distribution calculations, simulated transfers, and transaction
history, and run the full automated acceptance suite.

My internal completion target is November 17, 2026. The tentative course
schedule lists Part 2 for November 21 and Part 3/HW5 for December 10.
I'll confirm the exact deadlines on Canvas and record actual completion
dates as I finish each milestone.