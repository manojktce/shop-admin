<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ShopSetting;
use App\Models\StockAdjustment;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->seed([RolePermissionSeeder::class, MasterSeeder::class]);

    $this->kg = Unit::firstWhere('short_name', 'kg');
    $this->qtl = Unit::firstWhere('short_name', 'qtl');
    $this->g = Unit::firstWhere('short_name', 'g');
    $this->litre = Unit::firstWhere('short_name', 'litre');
    $this->pcs = Unit::firstWhere('short_name', 'pcs');
    $this->gst5 = Tax::firstWhere('name', 'GST 5%');
    $this->cat = Category::create(['name' => 'Grocery', 'sort_order' => 1, 'is_active' => true]);
    $this->rice = stockProduct($this);
});

function stockProduct($test, array $overrides = []): Product
{
    return Product::create(array_merge([
        'code' => 'R01', 'name' => 'Sona Masoori Rice', 'category_id' => $test->cat->id,
        'base_unit_id' => $test->kg->id, 'tax_id' => $test->gst5->id, 'selling_price' => 60, 'cost_price' => 0,
        'allow_decimal_qty' => true, 'min_stock' => 50, 'is_active' => true,
    ], $overrides));
}

function entryInput($test, ?array $items = null, array $overrides = []): array
{
    return array_merge([
        'entry_date' => now()->toDateString(),
        'notes' => 'Supplier bill 12',
        'items' => $items ?? [['product_id' => $test->rice->id, 'unit_id' => $test->qtl->id, 'qty' => '1', 'cost_per_base_unit' => '50']],
    ], $overrides);
}

function adjInput($test, array $overrides = []): array
{
    return array_merge([
        'product_id' => $test->rice->id, 'adjustment_date' => now()->toDateString(),
        'base_qty' => '5', 'reason' => 'damage', 'notes' => null,
    ], $overrides);
}

test('guests are redirected from stock pages', function () {
    foreach (['stock', 'stock-entries', 'stock-entries/create', 'stock-adjustments', 'stock-adjustments/create', "stock/{$this->rice->id}/ledger"] as $path) {
        $this->get("/admin/{$path}")->assertRedirect();
    }
    $this->post('/admin/stock-entries', entryInput($this))->assertRedirect();
    $this->post('/admin/stock-adjustments', adjInput($this))->assertRedirect();
    expect(StockEntry::count())->toBe(0);
});

test('staff and admin can use stock pages and record stock in', function (bool $admin) {
    $user = $admin ? User::factory()->admin()->create() : User::factory()->create();
    $this->actingAs($user);

    foreach (['stock', 'stock?low=1', 'stock-entries', 'stock-entries/create', 'stock-adjustments', 'stock-adjustments/create', "stock/{$this->rice->id}/ledger"] as $path) {
        $this->get("/admin/{$path}")->assertOk();
    }

    $this->post('/admin/stock-entries', entryInput($this))->assertSessionHasNoErrors()->assertRedirect();
    expect(StockEntry::first()->created_by)->toBe($user->id)
        ->and($this->rice->fresh()->current_stock)->toBe('100.000');
})->with([[false], [true]]);

test('stock in converts the unit to base quantity and computes amount, cost and entry number', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/admin/stock-entries', entryInput($this))->assertSessionHasNoErrors();
    $entry = StockEntry::with('items')->first();
    $item = $entry->items->first();

    expect($entry->entry_no)->toBe('SIN-0001')
        ->and($item)->qty->toBe('1.000')->base_qty->toBe('100.000')->cost_per_base_unit->toBe('50.00')->amount->toBe('5000.00')
        ->and($item->unit_id)->toBe($this->qtl->id)
        ->and($this->rice->fresh())->current_stock->toBe('100.000')->cost_price->toBe('50.00');

    $this->post('/admin/stock-entries', entryInput($this))->assertSessionHasNoErrors();
    expect(StockEntry::latest('id')->first()->entry_no)->toBe('SIN-0002');

    $this->get("/admin/stock-entries/{$entry->id}")->assertOk()->assertSee('SIN-0001')->assertSee('5,000.00');
});

test('multi line entry writes one movement per line with running balance', function () {
    $this->actingAs($u = User::factory()->create());
    $dal = stockProduct($this, ['code' => 'D01', 'name' => 'Dal']);

    $this->post('/admin/stock-entries', entryInput($this, [
        ['product_id' => $this->rice->id, 'unit_id' => $this->kg->id, 'qty' => '10', 'cost_per_base_unit' => '40'],
        ['product_id' => $this->rice->id, 'unit_id' => $this->kg->id, 'qty' => '2.5', 'cost_per_base_unit' => '45'],
        ['product_id' => $dal->id, 'unit_id' => $this->qtl->id, 'qty' => '1', 'cost_per_base_unit' => '90'],
        ['product_id' => '', 'unit_id' => '', 'qty' => '', 'cost_per_base_unit' => ''],
    ]))->assertSessionHasNoErrors();

    $rice = StockMovement::where('product_id', $this->rice->id)->orderBy('id')->get();
    expect(StockMovement::count())->toBe(3)
        ->and($rice->pluck('balance_after')->all())->toBe(['10.000', '12.500'])
        ->and($rice->pluck('base_qty')->all())->toBe(['10.000', '2.500'])
        ->and($rice->first())->type->toBe('stock_in')->reference_type->toBe('stock_entry_items')->created_by->toBe($u->id)
        ->and($rice->first()->reference_id)->toBe(StockEntry::first()->items()->orderBy('id')->first()->id)
        ->and($this->rice->fresh())->current_stock->toBe('12.500')->cost_price->toBe('45.00')
        ->and($dal->fresh()->current_stock)->toBe('100.000');
});

test('stock in is validated', function (array $overrides, string $error) {
    $this->actingAs(User::factory()->create());
    $inactive = stockProduct($this, ['code' => 'X01', 'is_active' => false]);
    $whole = stockProduct($this, ['code' => 'P01', 'base_unit_id' => $this->pcs->id, 'allow_decimal_qty' => false]);
    $item = fn (array $o) => [array_merge(['product_id' => $this->rice->id, 'unit_id' => $this->kg->id, 'qty' => '1', 'cost_per_base_unit' => '10'], $o)];
    $map = [
        'litre_for_kg' => $item(['unit_id' => $this->litre->id]),
        'fraction_whole' => $item(['product_id' => $whole->id, 'unit_id' => $this->pcs->id, 'qty' => '1.5']),
        'inactive' => $item(['product_id' => $inactive->id]),
        'zero_qty' => $item(['qty' => '0']),
        'negative_cost' => $item(['cost_per_base_unit' => '-1']),
        'no_items' => [],
    ];
    $input = isset($overrides['case']) ? entryInput($this, $map[$overrides['case']]) : entryInput($this, null, $overrides);

    $this->post('/admin/stock-entries', $input)->assertSessionHasErrors($error);
    expect(StockEntry::count())->toBe(0)->and(StockMovement::count())->toBe(0);
})->with([
    'other base unit' => [['case' => 'litre_for_kg'], 'items.0.unit_id'],
    'fraction on non-loose product' => [['case' => 'fraction_whole'], 'items.0.qty'],
    'inactive product' => [['case' => 'inactive'], 'items.0.product_id'],
    'zero qty' => [['case' => 'zero_qty'], 'items.0.qty'],
    'negative cost' => [['case' => 'negative_cost'], 'items.0.cost_per_base_unit'],
    'no items' => [['case' => 'no_items'], 'items'],
    'future date' => [['entry_date' => '2999-01-01'], 'entry_date'],
    'missing date' => [['entry_date' => ''], 'entry_date'],
]);

test('a whole product accepts a derived unit giving whole base units', function () {
    $this->actingAs(User::factory()->create());
    $whole = stockProduct($this, ['code' => 'P01', 'base_unit_id' => $this->pcs->id, 'allow_decimal_qty' => false]);
    $box = Unit::firstWhere('short_name', 'box');

    $this->post('/admin/stock-entries', entryInput($this, [['product_id' => $whole->id, 'unit_id' => $box->id, 'qty' => '2', 'cost_per_base_unit' => '5']]))
        ->assertSessionHasNoErrors();
    expect($whole->fresh()->current_stock)->toBe('24.000');
});

test('adjustments add and reduce stock through the ledger', function () {
    $this->actingAs($u = User::factory()->create());
    $this->post('/admin/stock-entries', entryInput($this))->assertSessionHasNoErrors();

    $this->post('/admin/stock-adjustments', adjInput($this, ['base_qty' => '-4.5', 'reason' => 'wastage']))->assertSessionHasNoErrors()->assertRedirect();
    $this->post('/admin/stock-adjustments', adjInput($this, ['base_qty' => '10', 'reason' => 'count_correction']))->assertSessionHasNoErrors();

    $m = StockMovement::where('type', 'adjustment')->orderBy('id')->get();
    expect($m->pluck('base_qty')->all())->toBe(['-4.500', '10.000'])
        ->and($m->pluck('balance_after')->all())->toBe(['95.500', '105.500'])
        ->and($m->first())->reference_type->toBe('stock_adjustments')->created_by->toBe($u->id)
        ->and(StockAdjustment::first())->created_by->toBe($u->id)
        ->and($this->rice->fresh()->current_stock)->toBe('105.500');
});

test('adjustment is validated', function (array $overrides, string $error) {
    $this->actingAs(User::factory()->create());
    $this->post('/admin/stock-adjustments', adjInput($this, $overrides))->assertSessionHasErrors($error);
    expect(StockAdjustment::count())->toBe(0)->and(StockMovement::count())->toBe(0);
})->with([
    'reason required' => [['reason' => ''], 'reason'],
    'bad reason' => [['reason' => 'theft'], 'reason'],
    'notes required for other' => [['reason' => 'other', 'notes' => ''], 'notes'],
    'zero' => [['base_qty' => '0'], 'base_qty'],
    'future date' => [['adjustment_date' => '2999-01-01'], 'adjustment_date'],
    'no product' => [['product_id' => ''], 'product_id'],
]);

test('adjustment with reason other and notes is accepted and non loose product needs whole qty', function () {
    $this->actingAs(User::factory()->create());
    $this->post('/admin/stock-adjustments', adjInput($this, ['reason' => 'other', 'notes' => 'Gift']))->assertSessionHasNoErrors();
    expect(StockAdjustment::first()->notes)->toBe('Gift');

    $whole = stockProduct($this, ['code' => 'P01', 'base_unit_id' => $this->pcs->id, 'allow_decimal_qty' => false]);
    $this->post('/admin/stock-adjustments', adjInput($this, ['product_id' => $whole->id, 'base_qty' => '1.5']))->assertSessionHasErrors('base_qty');
    $this->post('/admin/stock-adjustments', adjInput($this, ['product_id' => $whole->id, 'base_qty' => '3']))->assertSessionHasNoErrors();
});

test('negative stock is blocked by default and allowed when the setting is on', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/admin/stock-adjustments', adjInput($this, ['base_qty' => '-1']))->assertSessionHasErrors('base_qty');
    expect(StockAdjustment::count())->toBe(0)->and(StockMovement::count())->toBe(0)->and($this->rice->fresh()->current_stock)->toBe('0.000');

    ShopSetting::put('allow_negative_stock', '1');
    $this->post('/admin/stock-adjustments', adjInput($this, ['base_qty' => '-1']))->assertSessionHasNoErrors();
    expect($this->rice->fresh()->current_stock)->toBe('-1.000')
        ->and(StockMovement::first()->balance_after)->toBe('-1.000');
});

test('ledger page shows movements and low stock list only products below minimum', function () {
    $this->actingAs(User::factory()->create());
    $ok = stockProduct($this, ['code' => 'O01', 'name' => 'Plenty Oil', 'min_stock' => 5]);
    $this->post('/admin/stock-entries', entryInput($this, [
        ['product_id' => $this->rice->id, 'unit_id' => $this->kg->id, 'qty' => '20', 'cost_per_base_unit' => '40'],
        ['product_id' => $ok->id, 'unit_id' => $this->kg->id, 'qty' => '10', 'cost_per_base_unit' => '40'],
    ]))->assertSessionHasNoErrors();
    $this->post('/admin/stock-adjustments', adjInput($this, ['base_qty' => '-2', 'reason' => 'damage']))->assertSessionHasNoErrors();

    $this->get("/admin/stock/{$this->rice->id}/ledger")->assertOk()
        ->assertSee('SIN-0001')->assertSee('Stock in')->assertSee('Adjustment - Damage')->assertSee('18');

    $this->get('/admin/stock?low=1')->assertOk()
        ->assertSee('Sona Masoori Rice')->assertDontSee('Plenty Oil')
        ->assertSee('32 kg'); // short by 50 - 18

    $this->get('/admin/stock')->assertOk()->assertSee('Sona Masoori Rice')->assertSee('Plenty Oil');
});

test('inactive products are not low stock', function () {
    $this->actingAs(User::factory()->create());
    stockProduct($this, ['code' => 'X01', 'name' => 'Retired Item', 'is_active' => false]);
    $this->get('/admin/stock?low=1')->assertDontSee('Retired Item');
});

test('a product with stock movements cannot be deleted', function () {
    $this->actingAs(User::factory()->create());
    $this->post('/admin/stock-entries', entryInput($this))->assertSessionHasNoErrors();

    $this->actingAs(User::factory()->admin()->create());
    $this->delete("/admin/products/{$this->rice->id}")->assertSessionHas('danger');
    expect(Product::find($this->rice->id))->not->toBeNull()
        ->and($this->rice->usedBy())->toBe('stock movements');
});

test('entries and adjustments have no edit or delete routes', function () {
    $this->actingAs(User::factory()->admin()->create());
    $this->post('/admin/stock-entries', entryInput($this))->assertSessionHasNoErrors();
    $this->post('/admin/stock-adjustments', adjInput($this))->assertSessionHasNoErrors();
    $entry = StockEntry::first();
    $adj = StockAdjustment::first();

    $this->get("/admin/stock-entries/{$entry->id}/edit")->assertNotFound();
    $this->put("/admin/stock-entries/{$entry->id}", [])->assertStatus(405);
    $this->delete("/admin/stock-entries/{$entry->id}")->assertStatus(405);
    $this->get("/admin/stock-adjustments/{$adj->id}/edit")->assertNotFound();
    expect($this->put("/admin/stock-adjustments/{$adj->id}", [])->getStatusCode())->toBeIn([404, 405]);
    expect($this->delete("/admin/stock-adjustments/{$adj->id}")->getStatusCode())->toBeIn([404, 405]);

    $names = collect(Route::getRoutes()->getRoutes())->map->getName()->filter()->all();
    expect($names)->not->toContain('stock-entries.edit', 'stock-entries.destroy', 'stock-adjustments.edit', 'stock-adjustments.destroy');
});

test('product edit stays admin only', function () {
    $this->actingAs(User::factory()->create());
    $this->get("/admin/products/{$this->rice->id}/edit")->assertForbidden();
});

test('a backdated entry does not replace the cost from a later dated entry', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post('/admin/stock-entries', entryInput($this))->assertSessionHasNoErrors();
    $old = [['product_id' => $this->rice->id, 'unit_id' => $this->kg->id, 'qty' => '10', 'cost_per_base_unit' => '40']];
    $this->actingAs($admin)->post('/admin/stock-entries', entryInput($this, $old, ['entry_date' => now()->subDays(5)->toDateString()]))->assertSessionHasNoErrors();

    expect((float) $this->rice->fresh()->cost_price)->toBe(50.0)
        ->and((float) $this->rice->fresh()->current_stock)->toBe(110.0);
});

test('stock beyond the storable maximum is a validation error', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post('/admin/stock-adjustments', adjInput($this, ['base_qty' => '999999999']))->assertSessionHasNoErrors();
    $this->actingAs($admin)->post('/admin/stock-adjustments', adjInput($this, ['base_qty' => '5']))->assertSessionHasErrors('base_qty');

    expect((float) $this->rice->fresh()->current_stock)->toBe(999999999.0);
});
