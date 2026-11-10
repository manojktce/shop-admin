# Module status

| # | Module | Status | Branch | Commit | PR | Evidence |
|---|---|---|---|---|---|---|
| 0 | Dependency fix | Merged to main (#1) | fix/composer-dependencies | 9d822a4 | https://github.com/manojktce/shop-admin/pull/1 | clean worktree: artisan boots; `php artisan test` 2/2 passed |
| 1 | Login & roles | Merged to main via #5 | feature/login-roles | bace91d | https://github.com/manojktce/shop-admin/pull/2 (stacked on #1) | `php artisan test` 22/22 passed; code-reviewer + security-auditor findings fixed |
| 2 | Shop settings | Merged to main via #5 | feature/shop-settings | d191ca3 | https://github.com/manojktce/shop-admin/pull/3 (stacked on #2) | `php artisan test` 35/35 passed; code-reviewer findings fixed |
| 3 | Masters | Merged to main (#4) | feature/masters | e5f6a77 | https://github.com/manojktce/shop-admin/pull/4 | `php8.5 artisan test` 71/71 passed (MastersTest 36); code-reviewer finding (factor precision) fixed; security-auditor: no issues |
| 4 | Products | PR created | feature/products | a7c508c | https://github.com/manojktce/shop-admin/pull/6 | `php8.5 artisan test` 101/101 passed (ProductsTest 30); code-reviewer 2 low findings fixed; security-auditor: no issues |
| 5 | Stock | In progress | feature/stock | | | `php8.4 artisan test` 131/131 passed (StockTest 30); code-reviewer + security-auditor: no High; overflow guard, ordered locking, backdated cost fixed; open: Staff cost visibility (decision) |
| 6 | Daily cash & expenses | Not started | | | | |
| 7 | Customers & credit | Not started | | | | |
| 8 | Web billing | Not started | | | | |
| 9 | Kiosk billing | Not started | | | | |
| 10 | Suppliers | Not started | | | | |
| 11 | Dashboard | Not started | | | | |
| 12 | Reports | Not started | | | | |
