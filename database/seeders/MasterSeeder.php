<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\PaymentMode;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Starter masters from the sample entries in requirements §3.3 and database-structure.xlsx
 * (units, taxes, payment_modes, expense_categories). Categories are shop specific and not seeded.
 * Only creates missing rows; never changes rows the Admin has edited.
 */
class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $kg = Unit::firstOrCreate(['short_name' => 'kg'], ['name' => 'Kilogram', 'conversion_factor' => 1]);
        $litre = Unit::firstOrCreate(['short_name' => 'litre'], ['name' => 'Litre', 'conversion_factor' => 1]);
        $pcs = Unit::firstOrCreate(['short_name' => 'pcs'], ['name' => 'Pieces', 'conversion_factor' => 1]);

        foreach ([
            ['g', 'Gram', $kg, 0.001],
            ['qtl', 'Quintal', $kg, 100],
            ['ml', 'Millilitre', $litre, 0.001],
            ['box', 'Box (12)', $pcs, 12],
        ] as [$short, $name, $base, $factor]) {
            // Skip if the base unit was changed into a derived unit by the Admin.
            if ($base->isBase()) {
                Unit::firstOrCreate(['short_name' => $short], ['name' => $name, 'base_unit_id' => $base->id, 'conversion_factor' => $factor]);
            }
        }

        foreach (['GST 0% (exempt)' => 0, 'GST 5%' => 5, 'GST 12%' => 12, 'GST 18%' => 18] as $name => $rate) {
            Tax::firstOrCreate(['name' => $name], ['rate' => $rate, 'is_active' => true]);
        }

        foreach (['Cash' => true, 'UPI' => false, 'Card' => false, 'Cheque' => false] as $name => $drawer) {
            PaymentMode::firstOrCreate(['name' => $name], ['affects_cash_drawer' => $drawer, 'is_active' => true]);
        }

        foreach (['Tea & snacks', 'Transport', 'Cleaning', 'Electricity'] as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
