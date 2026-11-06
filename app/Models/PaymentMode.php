<?php

namespace App\Models;

use App\Models\Concerns\GuardsUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** How money is paid or received; affects_cash_drawer = counts in daily cash (requirements §3.3; XLS → payment_modes). */
#[Fillable(['name', 'affects_cash_drawer', 'is_active'])]
class PaymentMode extends Model
{
    use GuardsUsage;

    /** Cash, customer, billing and supplier modules add their relations. */
    protected static array $usageRelations = [];

    protected function casts(): array
    {
        return ['affects_cash_drawer' => 'boolean', 'is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
