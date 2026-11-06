<?php

namespace App\Models;

use App\Models\Concerns\GuardsUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Unit of measure (requirements §3.3; XLS → units).
 * 1 unit = conversion_factor × base unit. A base unit has base_unit_id NULL and factor 1;
 * stock is always stored in the base unit.
 */
#[Fillable(['name', 'short_name', 'base_unit_id', 'conversion_factor'])]
class Unit extends Model
{
    use GuardsUsage;

    /** Stock and billing modules add their relations. */
    protected static array $usageRelations = [
        'derivedUnits' => 'other units',
        'products' => 'products',
        'quickQtys' => 'product quick quantities',
    ];

    protected function casts(): array
    {
        return ['conversion_factor' => 'decimal:6'];
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function derivedUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    /** Products whose stock is counted in this (base) unit. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'base_unit_id');
    }

    public function quickQtys(): HasMany
    {
        return $this->hasMany(ProductQuickQty::class);
    }

    /** Used by products or their quick quantities: base unit and factor must not change. */
    public function isUsedByProducts(): bool
    {
        return $this->products()->exists() || $this->quickQtys()->exists();
    }

    public function isBase(): bool
    {
        return $this->base_unit_id === null;
    }

    /** The base unit this unit converts to (itself for a base unit). */
    public function baseUnitId(): int
    {
        return $this->base_unit_id ?? $this->id;
    }

    /** Quantity in this unit expressed in the base unit, e.g. 1 qtl → 100 (kg). */
    public function toBase(string|float|int $quantity): float
    {
        return (float) $quantity * (float) $this->conversion_factor;
    }

    public function scopeBase($query)
    {
        return $query->whereNull('base_unit_id');
    }
}
