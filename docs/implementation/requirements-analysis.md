# Requirements analysis – Shop Admin Phase 1

## Sources
| Ref | File | Content |
|---|---|---|
| DOC | `requirements/project-requirements.docx` | Phase 1 project documentation, sections 1–9 (text and tables fully extracted; no embedded images) |
| XLS | `requirements/database-structure.xlsx` | 25 sheets, all visible: `Overview` + one sheet per table (24 tables). No formulas, comments or merged cells |

Source references are written `DOC §x.y` or `XLS → sheet → rows`.

## Scope
Phase 1: 12 modules (DOC §2). Out of scope (DOC §9.2): barcode, online ordering, weighing-scale integration, multi-branch, linking stock-in to supplier bills. Phase 2/3 (discounts, returns, profit reports, mobile app) not included.

## Glossary
DOC §9.3: base unit, conversion factor, stock ledger, credit sale, receipt, outstanding, petty expense, day open/close, kiosk billing.

## Business rules (explicit)
| ID | Rule | Source |
|---|---|---|
| BR-01 | Roles Admin (everything) and Staff (billing web+kiosk, customer receipts, petty expenses, stock view) | DOC §1, §3.1 |
| BR-02 | Every bill, receipt, expense records who entered it | DOC §3.1 |
| BR-03 | Invoice numbers continue automatically (prefix + counter) | DOC §3.2; XLS → shop_settings → r15–16 |
| BR-04 | Bill rounding: nearest ₹1 or exact paise; prices include GST | DOC §3.2; XLS → products → E11 |
| BR-05 | Unit = factor × base unit; stock always stored in base unit | DOC §3.3; XLS → units |
| BR-06 | Every stock in / sale / adjustment / cancel writes one stock ledger row; current stock = sum | DOC §4.4; XLS → stock_movements |
| BR-07 | Adjustment needs a reason and the user | DOC §4.3; XLS → stock_adjustments → E9 |
| BR-08 | Stock in is not linked to supplier bills | DOC §4.2, §5.2 |
| BR-09 | Credit sale: unpaid part is added to customer balance; receipts reduce it | DOC §5.1; XLS → customer_ledger |
| BR-10 | Split payments allowed | DOC §6.1; XLS → sale_payments |
| BR-11 | Bills cannot be deleted; Admin cancels with reason, stock and balances reversed | DOC §6.4 |
| BR-12 | Stock may go below zero only if Admin enables it | DOC §6.4 |
| BR-13 | Only one day open at a time; billing/receipts/expenses recorded against it | DOC §7.1 |
| BR-14 | Expected cash = Opening + Cash sales + Cash receipts + Cash added − Cash expenses − Cash supplier payments − Cash withdrawn | DOC §7.3 |
| BR-15 | Closed day accepts no entries unless Admin reopens; closing cash may carry forward | DOC §7.3 |
| BR-16 | Admin can edit or cancel an expense with a reason | DOC §7.2 |
| BR-17 | Supplier bills increase payable, payments (partial allowed) reduce it | DOC §5.2 |

## Decisions made by the user (2026-11-06)
| ID | Question | Decision |
|---|---|---|
| D-01 | Existing app models "Customer" as a login user | Replace with the spec's `customers` table; drop the Customer login role |
| D-02 | Users: email login + spatie roles vs sheet | Follow sheet literally: `username`, `role` enum(admin, staff), `is_active`. Spatie stays as the permission engine and mirrors `users.role` |
| D-03 | Credit limit exceeded | Staff blocked; Admin may override |
| D-04 | Report export Excel/PDF | Install `maatwebsite/excel` + `barryvdh/laravel-dompdf` (installed by user) |

## Assumptions (non-blocking, documented)
| ID | Assumption | Reason |
|---|---|---|
| A-01 | Daily cash module built before Customers/Billing | XLS: `sales.cash_day_id`, `customer_receipts.cash_day_id` are NOT NULL |
| A-02 | Overview "day close approval" = Admin reopens closed days; Staff can open/close | DOC §7.3 says staff closes |
| A-03 | Staff may create customers (credit limit forced to 0) and record stock in / adjustments | DOC §4.2 "Staff or Admin"; §4.3 samples by staff |
| A-04 | Walk-in customer identified by setting `walk_in_customer_id`; Walk-in bills must be fully paid | XLS → sales → E8 "1 = Walk-in" |
| A-05 | Customer ledger gets a row only when a bill leaves a balance (`debit` = unpaid amount) | XLS → customer_ledger samples r19–21 |
| A-06 | Receipt/stock-in numbers use prefix + counter settings like invoices | XLS samples RCP-0021, SIN-0001 |
| A-07 | `sales.cancel_reason` and `cash_movements.cancel_reason` columns added | DOC §6.4/§7.2 require a reason; XLS has no column |
| A-08 | A bill can be cancelled only while its day is open | BR-15 |
| A-09 | Customer receipt amount cannot exceed balance due | Not specified; prevents negative balances |
| A-11 | Invoice prefix includes the year (`INV-2026-`); it does not roll over automatically – Admin edits the prefix (and may reset the counter) at year change | DOC §3.2 example only |
| A-12 | Settings counters can be raised but never lowered below numbers already used | Prevents duplicate document numbers |
| A-13 | Masters screens are Admin only; Staff reads masters only through other screens (billing, expenses) | DOC §1 "Admin: … masters" |
| A-14 | Units have one level: a derived unit points to a base unit (no chains); a unit that others are based on stays a base unit; base unit factor forced to 1 | DOC §3.3 table; XLS → units → E8–E9 |
| A-15 | Tax rate 0–100 with up to 2 decimals; tax names need not be unique | XLS → taxes (no UNIQUE on name) |
| A-16 | Starter data seeds the sample units (kg, g, qtl, litre, ml, pcs, box), taxes, payment modes and expense categories; categories are shop specific and not seeded | DOC §3.3 samples; XLS sample rows |
| A-17 | In-use guard: a master referenced by other records cannot be deleted (inactive is offered instead). In Module 3 only units reference units; Modules 4–10 register their relations in `$usageRelations` | Module plan |
| A-10 | Supplier bill attachments stored on the private disk, downloaded through an authorised route | Security |

## Dependency issue found
* `maatwebsite/excel: "*"` resolved to v1.1.5 and broke every artisan command. User chose to pin `^4.0` (works on Laravel 13). Lock update also moved `laravel/framework` 13.12 → 13.40. Fixed in PR #1.

## Existing-code conflicts found
* `CustomerController` manages users with a Customer role → replaced (D-01).
* Shared `ResponseTrait` called `redirect(null)->with()`, which throws for non-AJAX forms → fixed in Module 1.
* Profile save (`AccountController`) never initialised the Crud trait and read a missing `password` key, so saving always failed → fixed in Module 1.
