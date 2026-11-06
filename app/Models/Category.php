<?php

namespace App\Models;

use App\Models\Concerns\GuardsUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Product group shown as a kiosk tile (requirements §3.3; XLS → categories). */
#[Fillable(['name', 'sort_order', 'is_active'])]
class Category extends Model
{
    use GuardsUsage;

    protected static array $usageRelations = ['products' => 'products'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
