<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Stock-in header (requirements §4.2). Immutable: corrections are made with stock adjustments. */
#[Fillable(['entry_no', 'entry_date', 'notes', 'created_by'])]
class StockEntry extends Model
{
    protected function casts(): array
    {
        return ['entry_date' => 'date'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockEntryItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
