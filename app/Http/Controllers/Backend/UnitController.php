<?php

namespace App\Http\Controllers\Backend;

use App\Models\Unit;
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
                ],
                // Ignored for a base unit (always 1).
                'conversion_factor' => [Rule::excludeIf(blank(request('base_unit_id'))), 'required', 'numeric', 'decimal:0,6', 'gt:0', 'max:999999'],
            ],
            'messages' => [
                'base_unit_id.exists' => 'Choose a base unit.',
                'base_unit_id.not_in' => 'A unit cannot be based on itself.',
                'base_unit_id.prohibited' => 'Other units are based on this unit, so it must stay a base unit.',
            ],
        ];
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
