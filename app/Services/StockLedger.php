<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShopSetting;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * The only writer of stock (requirements §4.2). Every change adds one signed stock_movements row
 * with the running balance and refreshes the cached products.current_stock.
 * Call inside the caller's DB::transaction so the document and its ledger rows commit together.
 */
class StockLedger
{
    /**
     * @param string $referenceType source table, e.g. 'stock_entry_items', 'stock_adjustments'
     * @param string $errorKey validation key used when the movement would make stock negative
     *
     * @throws ValidationException when stock would go below zero and negative stock is not allowed
     */
    public static function record(
        Product|int $product,
        string $type,
        float $baseQty,
        string $referenceType,
        int $referenceId,
        ?Carbon $date = null,
        string $errorKey = 'stock',
    ): StockMovement {
        $id = $product instanceof Product ? $product->id : $product;

        // Lock the product row so two movements on the same product never read the same balance.
        $locked = Product::whereKey($id)->lockForUpdate()->firstOrFail();
        $balance = round((float) $locked->current_stock + $baseQty, 3);

        // Columns are decimal(12,3).
        if (abs($balance) > 999999999.999) {
            throw ValidationException::withMessages([
                $errorKey => "Stock of {$locked->name} would exceed the maximum that can be stored.",
            ]);
        }

        if ($balance < 0 &&ShopSetting::get('allow_negative_stock') !== '1') {
            throw ValidationException::withMessages([
                $errorKey => "Not enough stock of {$locked->name}: available " . rtrim(rtrim(number_format((float) $locked->current_stock, 3, '.', ''), '0'), '.') . '.',
            ]);
        }

        $locked->current_stock = $balance;
        $locked->save();

        if ($product instanceof Product) {
            $product->current_stock = $balance;
            $product->syncOriginalAttribute('current_stock');
        }

        return StockMovement::create([
            'product_id' => $id,
            'movement_date' => $date ?? now(),
            'type' => $type,
            'base_qty' => $baseQty,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'balance_after' => $balance,
            'created_by' => auth()->id(),
        ]);
    }
}
