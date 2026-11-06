# Requirement traceability matrix

| Req | Source | Module | Acceptance criteria | Status | Tests |
|---|---|---|---|---|---|
| R-01 | DOC §3.1; XLS → users | 1 | Login with username + password; disabled users cannot log in | Implemented | LoginRolesTest: login, wrong password, disabled, disabled mid-session |
| R-02 | DOC §1, §3.1 | 1 | Admin can do everything; Staff limited to billing, receipts, expenses, stock view | Implemented (later modules add their permissions) | LoginRolesTest: role mirror, admin gate, staff forbidden |
| R-03 | DOC §3.1 | 1 | Admin adds users, disables them, resets passwords | Implemented | LoginRolesTest: create, validate, disable + reset, blank keeps password, no self-demote, profile cannot escalate |
| R-04 | DOC §3.2; XLS → shop_settings | 2 | Shop details, numbering, print size, rounding saved and used | Not started | |
| R-05 | DOC §3.3; XLS → categories, taxes, payment_modes, expense_categories | 3 | CRUD for each master; in-use masters cannot be deleted | Not started | |
| R-06 | DOC §3.3; XLS → units | 3 | Unit has base unit + factor; base unit factor = 1 | Not started | |
| R-07 | DOC §4.1; XLS → products, product_quick_qtys | 4 | Product fields per table; code unique; quick quantities | Not started | |
| R-08 | DOC §4.2; XLS → stock_entries/items | 5 | Stock in any unit converts to base (1 qtl = +100 kg) | Not started | |
| R-09 | DOC §4.3; XLS → stock_adjustments | 5 | Adjustment with reason + user | Not started | |
| R-10 | DOC §4.4; XLS → stock_movements | 5 | One ledger row per change with running balance; low-stock list | Not started | |
| R-11 | DOC §7.1, §7.3; XLS → cash_days | 6 | One open day; close computes expected/difference; note when not zero; Admin reopen | Not started | |
| R-12 | DOC §7.2; XLS → cash_movements | 6 | Expenses, withdrawals, cash added; Admin edit/cancel with reason | Not started | |
| R-13 | DOC §7.5 | 6 | Day-wise register with drill-down | Not started | |
| R-14 | DOC §5.1; XLS → customers, customer_ledger, customer_receipts | 7 | Customers, receipts, statement with running balance, purchase history | Not started | |
| R-15 | DOC §6.1, §6.3; XLS → sales, sale_items, sale_payments | 8 | Bill with GST-inclusive lines, split/credit payment, invoice print 80 mm/A4, reprint | Not started | |
| R-16 | DOC §6.4 | 8 | No delete; Admin cancel with reason reverses stock and balance; negative-stock setting | Not started | |
| R-17 | DOC §6.2 | 9 | Kiosk: category tabs, tiles, quick qty, same bill result | Not started | |
| R-18 | DOC §5.2; XLS → suppliers, supplier_bills, supplier_payments | 10 | Bills, partial payments, statement, due list; stock unaffected | Not started | |
| R-19 | DOC §8.1 | 11 | Dashboard tiles | Not started | |
| R-20 | DOC §8.2 | 12 | 12 reports with filters and Excel/PDF export | Not started | |
