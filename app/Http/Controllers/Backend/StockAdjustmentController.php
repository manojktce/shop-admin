<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Services\StockLedger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Stock adjustments (requirements §4.2; XLS → stock_adjustments). Open to Admin and Staff (A-03).
 * Immutable: there is no edit or delete; reverse a mistake with another adjustment.
 */
class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $adjustments = StockAdjustment::query()
            ->with(['product:id,code,name,base_unit_id', 'product.baseUnit:id,short_name', 'creator:id,name'])
            ->orderByDesc('adjustment_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        return view('backend.stock_adjustment.list', compact('adjustments'));
    }

    public function create()
    {
        return view('backend.stock_adjustment.create', [
            'products' => Product::with('baseUnit:id,short_name')->orderBy('name')->get(['id', 'code', 'name', 'base_unit_id', 'is_active']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'adjustment_date' => 'required|date_format:Y-m-d|before_or_equal:today',
            'base_qty' => [
                'required', 'numeric', 'not_in:0', 'between:-999999999,999999999', 'decimal:0,3',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $product = Product::find((int) $request->input('product_id'));

                    if ($product && is_numeric($value) && ($error = StockEntryController::baseQtyError($product, (float) $value))) {
                        $fail($error);
                    }
                },
            ],
            'reason' => ['required', Rule::in(array_keys(StockAdjustment::REASONS))],
            'notes' => [Rule::requiredIf($request->input('reason') === 'other'), 'nullable', 'string', 'max:255'],
        ], [
            'adjustment_date.before_or_equal' => 'The adjustment date cannot be in the future.',
            'base_qty.not_in' => 'The quantity cannot be zero.',
            'notes.required' => 'Notes are required when the reason is Other.',
        ]);

        DB::transaction(function () use ($request) {
            $adjustment = StockAdjustment::create([
                'product_id' => (int) $request->product_id,
                'adjustment_date' => $request->adjustment_date,
                'base_qty' => $request->base_qty,
                'reason' => $request->reason,
                'notes' => filled($request->notes) ? trim($request->notes) : null,
                'created_by' => auth()->id(),
            ]);

            StockLedger::record(
                $adjustment->product_id, StockMovement::ADJUSTMENT, (float) $request->base_qty, 'stock_adjustments', $adjustment->id,
                Carbon::parse($request->adjustment_date)->setTimeFrom(now()), 'base_qty',
            );
        });

        return redirect(baseURL('stock-adjustments'))->with('success', 'Stock adjustment saved successfully.');
    }
}
