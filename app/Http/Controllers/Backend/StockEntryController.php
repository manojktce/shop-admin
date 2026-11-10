<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockEntryItem;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Services\NumberSequence;
use App\Services\StockLedger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Stock in (requirements §4.2; XLS → stock_entries, stock_entry_items). Open to Admin and Staff (A-03).
 * Entries are immutable: there is no edit or delete; corrections are made with stock adjustments.
 */
class StockEntryController extends Controller
{
    public const MAX_ITEMS = 50;

    public function index(Request $request)
    {
        $entries = StockEntry::query()
            ->withCount('items')
            ->withSum('items', 'amount')
            ->with('creator:id,name')
            ->when($request->filled('q'), fn ($q) => $q->where('entry_no', 'like', '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($request->q)) . '%'))
            ->orderByDesc('entry_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        return view('backend.stock_entry.list', compact('entries'));
    }

    public function create()
    {
        return view('backend.stock_entry.create', $this->formData());
    }

    public function store(Request $request)
    {
        // Blank rows left in the form are ignored.
        $items = collect($request->input('items', []))
            ->filter(fn ($row) => is_array($row) && (filled($row['product_id'] ?? null) || filled($row['qty'] ?? null) || filled($row['cost_per_base_unit'] ?? null)))
            ->values()->all();
        $request->merge(['items' => $items]);

        $products = Product::active()->get()->keyBy('id');
        $units = Unit::all()->keyBy('id');

        $request->validate([
            'entry_date' => 'required|date_format:Y-m-d|before_or_equal:today',
            'notes' => 'nullable|string|max:255',
            'items' => 'required|array|min:1|max:' . self::MAX_ITEMS,
            'items.*.product_id' => ['required', 'integer', fn ($a, $v, $fail) => $products->has((int) $v) ?: $fail('Choose an active product.')],
            'items.*.unit_id' => [
                'required', 'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($products, $units, $request) {
                    $unit = $units->get((int) $value);
                    $product = $products->get((int) $request->input(str_replace('unit_id', 'product_id', $attribute)));

                    if (! $unit) {
                        $fail('Choose a unit.');
                    } elseif ($product && $unit->baseUnitId() !== (int) $product->base_unit_id) {
                        $fail('This unit does not convert to the product\'s base unit.');
                    }
                },
            ],
            'items.*.qty' => [
                'required', 'numeric', 'gt:0', 'max:999999999', 'decimal:0,3',
                function (string $attribute, mixed $value, Closure $fail) use ($products, $units, $request) {
                    $product = $products->get((int) $request->input(str_replace('qty', 'product_id', $attribute)));
                    $unit = $units->get((int) $request->input(str_replace('qty', 'unit_id', $attribute)));

                    if ($product && $unit && is_numeric($value) && $unit->baseUnitId() === (int) $product->base_unit_id) {
                        if ($error = self::baseQtyError($product, $unit->toBase($value))) {
                            $fail($error);
                        }
                    }
                },
            ],
            'items.*.cost_per_base_unit' => 'required|numeric|min:0|max:9999999999|decimal:0,2',
        ], [
            'items.required' => 'Add at least one product.',
            'items.min' => 'Add at least one product.',
            'items.max' => 'Up to ' . self::MAX_ITEMS . ' products per entry.',
            'entry_date.before_or_equal' => 'The entry date cannot be in the future.',
        ]);

        $entry = DB::transaction(function () use ($request, $items) {
            $entry = StockEntry::create([
                'entry_no' => NumberSequence::next(NumberSequence::STOCK_ENTRY),
                'entry_date' => $request->entry_date,
                'notes' => filled($request->notes) ? trim($request->notes) : null,
                'created_by' => auth()->id(),
            ]);

            $date = Carbon::parse($request->entry_date)->setTimeFrom(now());

            // Lock all product rows in id order up front so concurrent entries cannot deadlock.
            Product::whereIn('id', collect($items)->pluck('product_id')->map(fn ($v) => (int) $v)->unique())
                ->orderBy('id')->lockForUpdate()->get(['id']);

            foreach ($items as $index => $row) {
                $unit = Unit::findOrFail((int) $row['unit_id']);
                $baseQty = round($unit->toBase($row['qty']), 3);
                $cost = round((float) $row['cost_per_base_unit'], 2);

                $item = $entry->items()->create([
                    'product_id' => (int) $row['product_id'],
                    'unit_id' => $unit->id,
                    'qty' => $row['qty'],
                    'base_qty' => $baseQty,
                    'cost_per_base_unit' => $cost,
                    'amount' => round($baseQty * $cost, 2),
                ]);

                StockLedger::record($item->product_id, StockMovement::STOCK_IN, $baseQty, 'stock_entry_items', $item->id, $date, "items.{$index}.qty");

                // Latest cost per base unit (XLS: products.cost_price): a backdated entry does not
                // replace the cost from a later-dated entry.
                $newerExists = StockEntryItem::where('product_id', $item->product_id)
                    ->whereHas('entry', fn ($q) => $q->whereDate('entry_date', '>', $request->entry_date))
                    ->exists();

                if (! $newerExists) {
                    Product::whereKey($item->product_id)->update(['cost_price' => $cost]);
                }
            }

            return $entry;
        });

        return redirect(baseURL("stock-entries/{$entry->id}"))->with('success', "Stock entry {$entry->entry_no} saved successfully.");
    }

    public function show(string $id)
    {
        $entry = StockEntry::with(['creator:id,name', 'items.product:id,code,name', 'items.unit:id,short_name', 'items.product.baseUnit:id,short_name'])->findOrFail($id);

        return view('backend.stock_entry.show', compact('entry'));
    }

    private function formData(): array
    {
        return [
            'products' => Product::active()->with('baseUnit:id,short_name')->orderBy('name')->get(['id', 'code', 'name', 'base_unit_id']),
            'units' => Unit::orderBy('name')->get(['id', 'name', 'short_name', 'base_unit_id']),
        ];
    }

    /** Stock is held in whole or 3-decimal base units; a product not sold loose needs whole units. */
    public static function baseQtyError(Product $product, float $baseQty): ?string
    {
        if (abs($baseQty - round($baseQty, 3)) > 1e-6 || round($baseQty, 3) == 0.0) {
            return 'This quantity is not a valid amount in the base unit (up to 3 decimals).';
        }

        if (! $product->allow_decimal_qty && abs($baseQty - round($baseQty)) > 1e-6) {
            return 'This product is not sold loose, so stock must be a whole number of base units.';
        }

        return null;
    }
}
