<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Manual correction; base_qty is signed (+ adds, - reduces). Immutable. */
#[Fillable(['product_id', 'adjustment_date', 'base_qty', 'reason', 'notes', 'created_by'])]
class StockAdjustment extends Model
{
    public const REASONS = [
        'opening' => 'Opening stock',
        'damage' => 'Damage',
        'wastage' => 'Wastage',
        'count_correction' => 'Count correction',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return ['adjustment_date' => 'date', 'base_qty' => 'decimal:3'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }
}
