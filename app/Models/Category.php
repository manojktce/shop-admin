<?php

namespace App\Models;

use App\Models\Concerns\GuardsUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Product group shown as a kiosk tile (requirements §3.3; XLS → categories). */
#[Fillable(['name', 'sort_order', 'is_active'])]
class Category extends Model
{
    use GuardsUsage;

    /** Products module adds 'products'. */
    protected static array $usageRelations = [];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
