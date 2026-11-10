<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One-tap quantity button; qty is in unit_id (requirements §4.1; XLS → product_quick_qtys). */
#[Fillable(['product_id', 'unit_id', 'qty', 'sort_order'])]
class ProductQuickQty extends Model
{
    protected $table = 'product_quick_qtys';

    protected function casts(): array
    {
        return ['qty' => 'decimal:3', 'sort_order' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** Button label, e.g. "250 g". */
    public function label(): string
    {
        return rtrim(rtrim((string) $this->qty, '0'), '.') . ' ' . $this->unit?->short_name;
    }

    /** Quantity in the product's base unit. */
    public function baseQty(): float
    {
        return $this->unit->toBase($this->qty);
    }
}
