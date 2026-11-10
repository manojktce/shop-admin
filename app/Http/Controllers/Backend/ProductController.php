<?php

namespace App\Http\Controllers\Backend;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Products (requirements §4.1; XLS → products, product_quick_qtys). Admin only: prices are Admin data (DOC §1).
 * current_stock is never set here; only the Stock module changes it through the stock ledger.
 */
class ProductController extends BaseController
{
    protected static string $permission = 'product';
    protected $module = 'product';
    protected $model = Product::class;

    public const MAX_QUICK_QTYS = 10;

    public function _shareData($page, $id = '')
    {
        $product = $this->record;

        return [
            'categories' => $this->allowed(Category::class, $product?->category_id)->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all(),
            'taxes' => $this->allowed(Tax::class, $product?->tax_id)->orderBy('rate')->pluck('name', 'id')->all(),
            'baseUnits' => Unit::base()->orderBy('name')->get()->mapWithKeys(fn ($u) => [$u->id => "{$u->name} ({$u->short_name})"])->all(),
            'units' => Unit::orderBy('name')->get(['id', 'name', 'short_name', 'base_unit_id']),
            'stockLocked' => $product && (float) $product->current_stock != 0,
        ];
    }

    public function _validation($id)
    {
        $product = $id ? Product::find($id) : null;

        // Blank quick-quantity rows left in the form are ignored.
        $quickQtys = collect(request()->input('quick_qtys', []))
            ->filter(fn ($row) => is_array($row) && (filled($row['unit_id'] ?? null) || filled($row['qty'] ?? null)))
            ->values()
            ->all();
        request()->merge(['quick_qtys' => $quickQtys]);

        $baseUnitId = (int) request('base_unit_id');
        $units = Unit::all()->keyBy('id');
        $compatibleUnitIds = $units->filter(fn ($u) => $u->baseUnitId() === $baseUnitId)->keys()->all();
        $baseUnitIds = $units->filter(fn ($u) => $u->isBase())->keys()->all();
        $stockLocked = $product && (float) $product->current_stock != 0;
        $fractionalStock = $product && abs((float) $product->current_stock - round((float) $product->current_stock)) > 1e-6;

        return [
            'rules' => [
                'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('products', 'code')->ignore($id)],
                'name' => 'required|string|max:150',
                'category_id' => ['required', 'integer', Rule::in($this->allowed(Category::class, $product?->category_id)->pluck('id')->all())],
                'tax_id' => ['required', 'integer', Rule::in($this->allowed(Tax::class, $product?->tax_id)->pluck('id')->all())],
                // Stock is held in the base unit, so it cannot change while the product has stock.
                'base_unit_id' => ['required', 'integer', Rule::in($stockLocked ? [$product->base_unit_id] : $baseUnitIds)],
                'selling_price' => 'required|numeric|min:0|max:9999999999|decimal:0,2',
                'cost_price' => 'nullable|numeric|min:0|max:9999999999|decimal:0,2',
                'min_stock' => 'nullable|numeric|min:0|max:999999999|decimal:0,3',
                // Fractional stock (e.g. 2.5 kg) can only be held by a product sold loose.
                'allow_decimal_qty' => [Rule::requiredIf($fractionalStock), Rule::when($fractionalStock, 'accepted')],
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'quick_qtys' => 'array|max:' . self::MAX_QUICK_QTYS,
                'quick_qtys.*.unit_id' => ['required', 'integer', Rule::in($compatibleUnitIds)],
                'quick_qtys.*.qty' => [
                    'required', 'numeric', 'gt:0', 'max:999999', 'decimal:0,3',
                    function (string $attribute, mixed $value, Closure $fail) use ($units) {
                        // A product not sold loose needs whole base units (e.g. 1 box = 12 pcs).
                        $unit = $units->get((int) request(str_replace('.qty', '.unit_id', $attribute)));

                        if ($unit && ! request()->boolean('allow_decimal_qty') && is_numeric($value)) {
                            $base = $unit->toBase($value);

                            if (abs($base - round($base)) > 1e-6) {
                                $fail('This product is not sold loose, so a quick quantity must be a whole number of base units.');
                            }
                        }
                    },
                ],
            ],
            'messages' => [
                'category_id.in' => 'Choose an active category.',
                'tax_id.in' => 'Choose an active tax.',
                'base_unit_id.in' => $stockLocked ? 'The base unit cannot be changed while the product has stock.' : 'Choose a base unit.',
                'quick_qtys.*.unit_id.in' => 'Quick quantity unit must convert to the product\'s base unit.',
                'allow_decimal_qty.required' => 'The product has fractional stock, so it must stay sold loose.',
                'allow_decimal_qty.accepted' => 'The product has fractional stock, so it must stay sold loose.',
                'quick_qtys.max' => 'Up to ' . self::MAX_QUICK_QTYS . ' quick quantities.',
            ],
        ];
    }

    public function _beforeSave($request)
    {
        $data = [
            'code' => trim($request['code']),
            'name' => trim($request['name']),
            'category_id' => (int) $request['category_id'],
            'tax_id' => (int) $request['tax_id'],
            'base_unit_id' => (int) $request['base_unit_id'],
            'selling_price' => $request['selling_price'],
            'cost_price' => $request['cost_price'] ?? 0,
            'min_stock' => $request['min_stock'] ?? 0,
            'allow_decimal_qty' => request()->boolean('allow_decimal_qty'),
            'is_active' => request()->boolean('is_active'),
        ];

        $oldImage = $this->record?->image_path;

        if (request()->hasFile('image')) {
            $newImage = request()->file('image')->store('products', 'public');
            $data['image_path'] = $newImage;
            // Do not leave the new file behind if the save is rolled back.
            DB::afterRollBack(fn () => Storage::disk('public')->delete($newImage));
        } elseif (request()->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        if ($oldImage && array_key_exists('image_path', $data)) {
            DB::afterCommit(fn () => Storage::disk('public')->delete($oldImage));
        }

        return $data;
    }

    /** Replace the quick quantities with the submitted rows, in form order. */
    public function _afterSave($product)
    {
        $product->quickQtys()->delete();

        foreach (request('quick_qtys', []) as $index => $row) {
            $product->quickQtys()->create([
                'unit_id' => (int) $row['unit_id'],
                'qty' => $row['qty'],
                'sort_order' => $index + 1,
            ]);
        }

        return true;
    }

    public function _afterDelete($product)
    {
        if ($product->image_path) {
            DB::afterCommit(fn () => Storage::disk('public')->delete($product->image_path));
        }

        return true;
    }

    /** Active records plus the one currently assigned (so editing keeps a since-deactivated value). */
    private function allowed(string $model, ?int $currentId)
    {
        return $model::query()->where(fn ($q) => $q->where('is_active', true)->when($currentId, fn ($q) => $q->orWhere('id', $currentId)));
    }
}
