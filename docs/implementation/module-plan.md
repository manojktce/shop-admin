# Module plan – one module = one branch = one PR (stacked)

Base branch: `main` (from `origin/HEAD`). Each module branch is created from the previous module branch; its PR targets that branch until the previous PR is merged, then it is retargeted to `main`.

| # | Module | Branch | Depends on | Tables (XLS) | Contents |
|---|---|---|---|---|---|
| 0 | Dependency fix (prerequisite) | `fix/composer-dependencies` | – | – | pin `maatwebsite/excel ^4.0` (v1.1.5 broke artisan on Laravel 13) and `barryvdh/laravel-dompdf ^3.1` – PR #1 |
| 1 | Login & roles | `feature/login-roles` | 0 | users (+roles/permissions) | username login, Admin/Staff roles + permission seeder, user management (add, disable, reset password), inactive-user logout, removes old Customer-as-login-user CRUD (D-01), CRUD redirect + profile-save fixes, test APP_KEY, these planning docs |
| 2 | Shop settings | `feature/shop-settings` | 1 | shop_settings | settings screen (name, address, phone, GSTIN, logo, numbering, print size, rounding, allow negative stock), number sequences |
| 3 | Masters | `feature/masters` | 1, 2 | categories, units, taxes, payment_modes, expense_categories | 5 CRUD screens, unit conversion rules, delete guard for masters in use, starter data seeder |
| 4 | Products | `feature/products` | 3 | products, product_quick_qtys | product CRUD, quick quantities, kiosk image, search by name/code |
| 5 | Stock | `feature/stock` | 4 | stock_entries, stock_entry_items, stock_adjustments, stock_movements | stock in (any unit), adjustments with reason, stock ledger per product, low-stock list |
| 6 | Daily cash & expenses | `feature/daily-cash` | 3 | cash_days, cash_movements | day open/close/reopen, expected cash, petty expenses, withdrawals, cash added, day-wise register |
| 7 | Customers & credit | `feature/customers` | 6 | customers, customer_ledger, customer_receipts | replaces old Customer-user CRUD; receipts; statement; purchase history |
| 8 | Web billing | `feature/web-billing` | 5, 7 | sales, sale_items, sale_payments | counter billing, GST-inclusive calculation, split/credit payment, credit-limit rule, invoice print (80 mm / A4), reprint, Admin cancel |
| 9 | Kiosk billing | `feature/kiosk-billing` | 8 | – | touch screen: category tabs, product tiles, quick quantities, same sale service |
| 10 | Suppliers | `feature/suppliers` | 6 | suppliers, supplier_bills, supplier_payments | suppliers, bills (attachment), payments (partial, cash hits drawer), statement, due list |
| 11 | Dashboard | `feature/dashboard` | 8, 10 | – | today/month sales, expenses, drawer cash, dues, low stock, top sellers |
| 12 | Reports | `feature/reports` | 11 | – | 12 reports from DOC §8.2 with filters, Excel and PDF export (adds composer packages) |

## Test plan
Pest feature tests per module (sqlite in-memory, `RefreshDatabase`): success, validation, authorization (Admin vs Staff vs guest), business rules (conversion, ledgers, expected cash, credit limit, negative stock, cancel reversal). Each PR runs `php artisan test`; final regression on the last branch runs the full suite.
