<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockEntryItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

/** Stock overview, low-stock list and per-product ledger (requirements §4.2). Open to Admin and Staff (A-03). */
class StockController extends Controller
{
    public function index(Request $request)
    {
        $low = $request->boolean('low');

        $products = Product::query()
            ->with('baseUnit:id,short_name')
            ->when($low, fn ($q) => $q->where('is_active', true)->whereColumn('current_stock', '<', 'min_stock'))
            ->search($request->q)
            ->when($low,
                fn ($q) => $q->orderByRaw('(min_stock - current_stock) desc'),
                fn ($q) => $q->orderBy('name'))
            ->paginate(25)->withQueryString();

        return view('backend.stock.list', compact('products', 'low'));
    }

    public function ledger(string $product)
    {
        $product = Product::with('baseUnit:id,short_name')->findOrFail($product);

        $movements = StockMovement::where('product_id', $product->id)
            ->orderByDesc('id')
            ->paginate(25);

        // Resolve the source document of each row (entry number or adjustment reason).
        $items = StockEntryItem::with('entry:id,entry_no')
            ->whereIn('id', $movements->where('reference_type', 'stock_entry_items')->pluck('reference_id'))->get()->keyBy('id');
        $adjustments = StockAdjustment::whereIn('id', $movements->where('reference_type', 'stock_adjustments')->pluck('reference_id'))->get()->keyBy('id');

        return view('backend.stock.ledger', compact('product', 'movements', 'items', 'adjustments'));
    }
}
