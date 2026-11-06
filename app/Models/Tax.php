<?php

namespace App\Models;

use App\Models\Concerns\GuardsUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** GST slab; rate is a percent (requirements §3.3; XLS → taxes). */
#[Fillable(['name', 'rate', 'is_active'])]
class Tax extends Model
{
    use GuardsUsage;

    /** Products module adds 'products'. */
    protected static array $usageRelations = [];

    protected function casts(): array
    {
        return ['rate' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
