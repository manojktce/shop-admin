<?php

namespace App\Models;

use App\Models\Concerns\GuardsUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Type of daily / petty expense (requirements §3.3; XLS → expense_categories). */
#[Fillable(['name', 'is_active'])]
class ExpenseCategory extends Model
{
    use GuardsUsage;

    /** Daily cash module adds 'cashMovements'. */
    protected static array $usageRelations = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
