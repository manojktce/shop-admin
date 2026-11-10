<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One stock-in line: qty in the unit entered, base_qty in the product base unit. */
#[Fillable(['stock_entry_id', 'product_id', 'unit_id', 'qty', 'base_qty', 'cost_per_base_unit', 'amount'])]
class StockEntryItem extends Model
{
    protected function casts(): array
    {
        return ['qty' => 'decimal:3', 'base_qty' => 'decimal:3', 'cost_per_base_unit' => 'decimal:2', 'amount' => 'decimal:2'];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(StockEntry::class, 'stock_entry_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
