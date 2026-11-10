<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Stock ledger row. Written only by App\Services\StockLedger. */
#[Fillable(['product_id', 'movement_date', 'type', 'base_qty', 'reference_type', 'reference_id', 'balance_after', 'created_by'])]
class StockMovement extends Model
{
    public const STOCK_IN = 'stock_in';
    public const SALE = 'sale';
    public const ADJUSTMENT = 'adjustment';
    public const SALE_CANCEL = 'sale_cancel';

    public const TYPES = [
        self::STOCK_IN => 'Stock in',
        self::SALE => 'Sale',
        self::ADJUSTMENT => 'Adjustment',
        self::SALE_CANCEL => 'Sale cancelled',
    ];

    protected function casts(): array
    {
        return ['movement_date' => 'datetime', 'base_qty' => 'decimal:3', 'balance_after' => 'decimal:3'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
