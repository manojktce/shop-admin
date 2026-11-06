<?php

namespace App\Models;

use App\Models\Concerns\GuardsUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Item sold (requirements §4.1; XLS → products). Prices are per base unit, GST inclusive.
 * current_stock (base unit) is changed only by the Stock module, never by the product form.
 */
#[Fillable(['code', 'name', 'category_id', 'base_unit_id', 'tax_id', 'selling_price', 'cost_price', 'allow_decimal_qty', 'min_stock', 'image_path', 'is_active'])]
class Product extends Model
{
    use GuardsUsage;

    /** Stock and billing modules add their relations. */
    protected static array $usageRelations = [];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'allow_decimal_qty' => 'boolean',
            'min_stock' => 'decimal:3',
            'current_stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function quickQtys(): HasMany
    {
        return $this->hasMany(ProductQuickQty::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Search by name or short code (requirements §4.1 "Short code for quick search"). */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';

        return $query->where(fn ($q) => $q->where('code', 'like', $like)->orWhere('name', 'like', $like));
    }

    public function isLowStock(): bool
    {
        return (float) $this->current_stock < (float) $this->min_stock;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? url('storage/' . $this->image_path) : null;
    }
}
