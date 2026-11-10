<?php

namespace App\Http\Controllers\Backend;

use App\Models\Unit;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Units master with conversion to a base unit (requirements §3.3; XLS → units). Admin only.
 * A base unit has no base unit and factor 1; any other unit points to a base unit with factor > 0.
 */
class UnitController extends BaseController
{
    protected static string $permission = 'unit';
    protected $module = 'unit';
    protected $model = Unit::class;

    public function _shareData($page, $id = '')
    {
        return [
            'baseUnits' => Unit::base()
                ->when($id !== '', fn ($query) => $query->whereKeyNot($id))
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn ($unit) => [$unit->id => "{$unit->name} ({$unit->short_name})"])
                ->all(),
        ];
    }

    public function _validation($id)
    {
        $hasDerivedUnits = $id && Unit::where('base_unit_id', $id)->exists();
        $unit = $id ? Unit::find($id) : null;

        return [
            'rules' => [
                'name' => 'required|string|max:50',
                'short_name' => ['required', 'string', 'max:10', Rule::unique('units', 'short_name')->ignore($id)],
                'base_unit_id' => [
                    'nullable',
                    'integer',
                    // Must be an existing base unit other than this unit; a unit others are based on stays a base unit.
                    Rule::exists('units', 'id')->whereNull('base_unit_id'),
                    Rule::notIn(array_filter([$id])),
                    Rule::prohibitedIf($hasDerivedUnits),
                    $this->lockedForProducts($unit, 'base_unit_id'),
                ],
                // Ignored for a base unit (always 1).
                'conversion_factor' => [Rule::excludeIf(blank(request('base_unit_id'))), 'required', 'numeric', 'decimal:0,6', 'gt:0', 'max:999999', $this->lockedForProducts($unit, 'conversion_factor')],
            ],
            'messages' => [
                'base_unit_id.exists' => 'Choose a base unit.',
                'base_unit_id.not_in' => 'A unit cannot be based on itself.',
                'base_unit_id.prohibited' => 'Other units are based on this unit, so it must stay a base unit.',
            ],
        ];
    }

    /**
     * Once products or quick quantities use a unit, its base unit and factor are fixed:
     * changing them would change the meaning of stock and quantities already recorded.
     * Implicit so it also runs when base_unit_id is cleared.
     */
    private function lockedForProducts(?Unit $unit, string $field): ValidationRule
    {
        return new class($unit, $field) implements ValidationRule {
            public bool $implicit = true;

            public function __construct(private ?Unit $unit, private string $field) {}

            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                if (! $this->unit || ! $this->unit->isUsedByProducts()) {
                    return;
                }

                $changed = $this->field === 'base_unit_id'
                    ? (filled($value) ? (int) $value : null) !== $this->unit->base_unit_id
                    : abs((float) $value - (float) $this->unit->conversion_factor) > 1e-9;

                if ($changed) {
                    $fail('This unit is used by products, so its base unit and conversion factor cannot be changed.');
                }
            }
        };
    }

    public function _beforeSave($request)
    {
        $baseUnitId = filled($request['base_unit_id'] ?? null) ? (int) $request['base_unit_id'] : null;

        return [
            'name' => trim($request['name']),
            'short_name' => trim($request['short_name']),
            'base_unit_id' => $baseUnitId,
            'conversion_factor' => $baseUnitId ? $request['conversion_factor'] : 1,
        ];
    }
}
