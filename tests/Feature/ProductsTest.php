<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductQuickQty;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->seed([RolePermissionSeeder::class, MasterSeeder::class]);

    $this->kg = Unit::firstWhere('short_name', 'kg');
    $this->g = Unit::firstWhere('short_name', 'g');
    $this->litre = Unit::firstWhere('short_name', 'litre');
    $this->ml = Unit::firstWhere('short_name', 'ml');
    $this->pcs = Unit::firstWhere('short_name', 'pcs');
    $this->box = Unit::firstWhere('short_name', 'box');
    $this->gst5 = Tax::firstWhere('name', 'GST 5%');
    $this->spices = Category::create(['name' => 'Spices & masala', 'sort_order' => 2, 'is_active' => true]);
});

function productInput($test, array $overrides = []): array
{
    return array_merge([
        'code' => 'S01',
        'name' => 'Chilli Powder',
        'category_id' => $test->spices->id,
        'base_unit_id' => $test->kg->id,
        'tax_id' => $test->gst5->id,
        'allow_decimal_qty' => '1',
        'selling_price' => '400',
        'cost_price' => '320',
        'min_stock' => '5',
        'is_active' => '1',
        'quick_qtys' => [
            ['qty' => '50', 'unit_id' => $test->g->id],
            ['qty' => '100', 'unit_id' => $test->g->id],
            ['qty' => '250', 'unit_id' => $test->g->id],
        ],
    ], $overrides);
}

function makeProduct($test, array $overrides = []): Product
{
    return Product::create(array_merge([
        'code' => 'R01', 'name' => 'Sona Masoori Rice', 'category_id' => $test->spices->id,
        'base_unit_id' => $test->kg->id, 'tax_id' => $test->gst5->id, 'selling_price' => 60, 'cost_price' => 52,
        'allow_decimal_qty' => true, 'min_stock' => 50, 'is_active' => true,
    ], $overrides));
}

$ajax = ['X-Requested-With' => 'XMLHttpRequest'];

// Access – prices and products are Admin data (DOC §1)
test('staff and guests cannot manage products', function () {
    $product = makeProduct($this);
    $this->get('/admin/products')->assertRedirect();

    Role::findByName(User::$staff)->givePermissionTo(['product.list', 'product.create', 'product.edit', 'product.delete']);
    $this->actingAs(User::factory()->create());

    $this->get('/admin/products')->assertForbidden();
    $this->get('/admin/products/create')->assertForbidden();
    $this->post('/admin/products', productInput($this))->assertForbidden();
    $this->put("/admin/products/{$product->id}", productInput($this))->assertForbidden();
    $this->delete("/admin/products/{$product->id}")->assertForbidden();
    expect(Product::count())->toBe(1);
});

// R-07: create with quick quantities (DOC §4.1 sample S01)
test('admin creates a product with quick quantities', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get('/admin/products/create')->assertOk()->assertSee('Quick quantities');

    $this->post('/admin/products', productInput($this))
        ->assertSessionHasNoErrors()
        ->assertRedirect(url('admin/products'))
        ->assertSessionHas('success', 'Product created successfully.');

    $product = Product::firstWhere('code', 'S01');
    expect($product)
        ->name->toBe('Chilli Powder')
        ->selling_price->toBe('400.00')
        ->cost_price->toBe('320.00')
        ->min_stock->toBe('5.000')
        ->current_stock->toBe('0.000')
        ->allow_decimal_qty->toBeTrue()
        ->is_active->toBeTrue();

    $quick = $product->quickQtys()->with('unit')->get();
    expect($quick->map->label()->all())->toBe(['50 g', '100 g', '250 g'])
        ->and($quick->pluck('sort_order')->all())->toBe([1, 2, 3])
        ->and($quick->first()->baseQty())->toBe(0.05);
});

test('current stock cannot be set from the product form', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/products', productInput($this, ['current_stock' => '999']))->assertSessionHasNoErrors();
    expect(Product::first()->current_stock)->toBe('0.000');
});

test('optional fields default to zero and blank quick rows are ignored', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/products', productInput($this, [
        'cost_price' => '', 'min_stock' => '', 'allow_decimal_qty' => null, 'is_active' => null,
        'quick_qtys' => [['qty' => '', 'unit_id' => ''], ['qty' => '1', 'unit_id' => $this->kg->id]],
    ]))->assertSessionHasNoErrors();

    $product = Product::first();
    expect($product)->cost_price->toBe('0.00')->min_stock->toBe('0.000')->allow_decimal_qty->toBeFalse()->is_active->toBeFalse()
        ->and($product->quickQtys()->count())->toBe(1);
});

test('product is validated', function (array $input, array $errors) {
    $this->actingAs(User::factory()->admin()->create());
    makeProduct($this, ['code' => 'R01']);

    $this->post('/admin/products', productInput($this, $input))->assertSessionHasErrors($errors);
    expect(Product::count())->toBe(1);
})->with([
    'required fields' => [['code' => '', 'name' => '', 'category_id' => '', 'base_unit_id' => '', 'tax_id' => '', 'selling_price' => ''], ['code', 'name', 'category_id', 'base_unit_id', 'tax_id', 'selling_price']],
    'duplicate code' => [['code' => 'R01'], ['code']],
    'code too long' => [['code' => str_repeat('A', 21)], ['code']],
    'code with spaces' => [['code' => 'R 01'], ['code']],
    'negative price' => [['selling_price' => '-1', 'cost_price' => '-1'], ['selling_price', 'cost_price']],
    'price with 3 decimals' => [['selling_price' => '1.005'], ['selling_price']],
    'negative min stock' => [['min_stock' => '-1'], ['min_stock']],
    'unknown category/tax' => [['category_id' => 999, 'tax_id' => 999], ['category_id', 'tax_id']],
]);

test('base unit must be a base unit', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/products', productInput($this, ['base_unit_id' => $this->g->id]))->assertSessionHasErrors('base_unit_id');
});

test('inactive category or tax cannot be chosen for a new product but stays valid on edit', function () {
    $this->actingAs(User::factory()->admin()->create());
    $soaps = Category::create(['name' => 'Soaps', 'sort_order' => 4, 'is_active' => false]);
    $old = Tax::create(['name' => 'Old GST', 'rate' => 28, 'is_active' => false]);

    $this->post('/admin/products', productInput($this, ['category_id' => $soaps->id, 'tax_id' => $old->id]))
        ->assertSessionHasErrors(['category_id' => 'Choose an active category.', 'tax_id' => 'Choose an active tax.']);

    $product = makeProduct($this, ['category_id' => $soaps->id, 'tax_id' => $old->id]);
    $this->get("/admin/products/{$product->id}/edit")->assertOk()->assertSee('Soaps');
    $this->put("/admin/products/{$product->id}", productInput($this, ['code' => 'R01', 'category_id' => $soaps->id, 'tax_id' => $old->id]))
        ->assertSessionHasNoErrors();
});

// Quick quantities
test('quick quantity unit must convert to the product base unit', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/products', productInput($this, ['quick_qtys' => [['qty' => '500', 'unit_id' => $this->ml->id]]]))
        ->assertSessionHasErrors(['quick_qtys.0.unit_id']);
});

test('quick quantities are validated', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/products', productInput($this, ['quick_qtys' => [
        ['qty' => '0', 'unit_id' => $this->g->id],
        ['qty' => '', 'unit_id' => $this->g->id],
        ['qty' => '1.0001', 'unit_id' => $this->kg->id],
        ['qty' => '1', 'unit_id' => ''],
    ]]))->assertSessionHasErrors(['quick_qtys.0.qty', 'quick_qtys.1.qty', 'quick_qtys.2.qty', 'quick_qtys.3.unit_id']);

    $tooMany = array_fill(0, 11, ['qty' => '1', 'unit_id' => $this->kg->id]);
    $this->post('/admin/products', productInput($this, ['quick_qtys' => $tooMany]))->assertSessionHasErrors('quick_qtys');
    expect(Product::count())->toBe(0);
});

test('product not sold loose needs whole base quantities in quick buttons', function () {
    $this->actingAs(User::factory()->admin()->create());
    $base = ['code' => 'SP01', 'name' => 'Soap', 'base_unit_id' => $this->pcs->id, 'allow_decimal_qty' => null];

    $this->post('/admin/products', productInput($this, $base + ['quick_qtys' => [['qty' => '0.1', 'unit_id' => $this->box->id]]]))
        ->assertSessionHasErrors('quick_qtys.0.qty');

    // 1 box = 12 pcs is a whole number.
    $this->post('/admin/products', productInput($this, $base + ['quick_qtys' => [['qty' => '1', 'unit_id' => $this->box->id], ['qty' => '6', 'unit_id' => $this->pcs->id]]]))
        ->assertSessionHasNoErrors();
    expect(Product::firstWhere('code', 'SP01')->quickQtys()->first()->baseQty())->toBe(12.0);
});

// Edit
test('admin edits a product and its quick quantities are replaced in order', function () {
    $this->actingAs(User::factory()->admin()->create());
    $product = makeProduct($this);
    $product->quickQtys()->create(['unit_id' => $this->kg->id, 'qty' => 1, 'sort_order' => 1]);

    $this->get("/admin/products/{$product->id}/edit")->assertOk()->assertSee('Sona Masoori Rice');

    $this->put("/admin/products/{$product->id}", productInput($this, [
        'code' => 'R01', 'name' => 'Sona Masoori Rice (new crop)', 'selling_price' => '62.50',
        'quick_qtys' => [['qty' => '5', 'unit_id' => $this->kg->id], ['qty' => '500', 'unit_id' => $this->g->id]],
    ]))->assertSessionHasNoErrors()->assertSessionHas('success', 'Product updated successfully.');

    $product->refresh();
    expect($product->name)->toBe('Sona Masoori Rice (new crop)')
        ->and($product->selling_price)->toBe('62.50')
        ->and($product->quickQtys()->with('unit')->get()->map->label()->all())->toBe(['5 kg', '500 g'])
        ->and(ProductQuickQty::count())->toBe(2);
});

test('base unit cannot change while the product has stock', function () {
    $this->actingAs(User::factory()->admin()->create());
    $product = makeProduct($this);
    $product->forceFill(['current_stock' => 85])->save();

    $this->get("/admin/products/{$product->id}/edit")->assertOk()->assertSee('Cannot change while the product has stock.');

    $this->put("/admin/products/{$product->id}", productInput($this, ['code' => 'R01', 'base_unit_id' => $this->litre->id, 'quick_qtys' => []]))
        ->assertSessionHasErrors(['base_unit_id' => 'The base unit cannot be changed while the product has stock.']);

    // Without stock the base unit may change.
    $product->forceFill(['current_stock' => 0])->save();
    $this->put("/admin/products/{$product->id}", productInput($this, ['code' => 'R01', 'base_unit_id' => $this->litre->id, 'quick_qtys' => []]))
        ->assertSessionHasNoErrors();
    expect($product->fresh()->base_unit_id)->toBe($this->litre->id);
});

test('sell loose cannot be switched off while stock is fractional', function () {
    $this->actingAs(User::factory()->admin()->create());
    $product = makeProduct($this);
    $product->forceFill(['current_stock' => 2.5])->save();

    $this->put("/admin/products/{$product->id}", productInput($this, ['code' => 'R01', 'allow_decimal_qty' => null, 'quick_qtys' => []]))
        ->assertSessionHasErrors(['allow_decimal_qty' => 'The product has fractional stock, so it must stay sold loose.']);
    expect($product->fresh()->allow_decimal_qty)->toBeTrue();

    $product->forceFill(['current_stock' => 3])->save();
    $this->put("/admin/products/{$product->id}", productInput($this, ['code' => 'R01', 'allow_decimal_qty' => null, 'quick_qtys' => []]))
        ->assertSessionHasNoErrors();
    expect($product->fresh()->allow_decimal_qty)->toBeFalse();
});

test('a new image is removed again when the save fails', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    // Force a failure after the image is stored: the quick-qty insert hits a missing table.
    Illuminate\Support\Facades\Schema::drop('product_quick_qtys');

    $this->post('/admin/products', productInput($this, ['image' => UploadedFile::fake()->image('s01.jpg')]))
        ->assertSessionHas('danger', 'Something went wrong.');

    expect(Product::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles('products'))->toBe([]);
});

// Image (kiosk tile)
test('image is stored, replaced and removed on the public disk', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/products', productInput($this, ['image' => UploadedFile::fake()->image('s01.jpg')]))->assertSessionHasNoErrors();
    $product = Product::first();
    $first = $product->image_path;
    expect($first)->toStartWith('products/');
    Storage::disk('public')->assertExists($first);

    $this->put("/admin/products/{$product->id}", productInput($this, ['image' => UploadedFile::fake()->image('new.png')]))->assertSessionHasNoErrors();
    $second = $product->fresh()->image_path;
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);

    // Saving without a new file keeps the image.
    $this->put("/admin/products/{$product->id}", productInput($this));
    expect($product->fresh()->image_path)->toBe($second);

    $this->put("/admin/products/{$product->id}", productInput($this, ['remove_image' => '1']))->assertSessionHasNoErrors();
    expect($product->fresh()->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($second);
});

test('image must be a small picture', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/products', productInput($this, ['image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]))
        ->assertSessionHasErrors('image');
    $this->post('/admin/products', productInput($this, ['image' => UploadedFile::fake()->image('big.jpg')->size(3000)]))
        ->assertSessionHasErrors('image');
});

// Delete
test('deleting a product removes its quick quantities and image', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    Storage::disk('public')->put('products/r01.jpg', 'x');
    $product = makeProduct($this, ['image_path' => 'products/r01.jpg']);
    $product->quickQtys()->create(['unit_id' => $this->kg->id, 'qty' => 1, 'sort_order' => 1]);

    $this->delete("/admin/products/{$product->id}")->assertSessionHas('success', 'Product deleted successfully.');

    expect(Product::count())->toBe(0)->and(ProductQuickQty::count())->toBe(0);
    Storage::disk('public')->assertMissing('products/r01.jpg');
});

// List and search
test('product list shows category, unit, price, stock and low-stock flag', function () use ($ajax) {
    $this->actingAs(User::factory()->admin()->create());
    makeProduct($this)->forceFill(['current_stock' => 12.5])->save();

    $this->get('/admin/products')->assertOk()->assertSee('Search by product name or short code.');
    $this->getJson('/admin/products', $ajax)->assertOk()->assertJsonFragment([
        'code' => 'R01',
        'category' => 'Spices &amp; masala',
        'selling_price' => '60.00 / kg',
        'current_stock' => '12.5 kg (low)',
        'min_stock' => '50 kg',
        'gst' => 'GST 5%',
    ]);
});

test('list search matches code or name', function () use ($ajax) {
    $this->actingAs(User::factory()->admin()->create());
    makeProduct($this);
    makeProduct($this, ['code' => 'D01', 'name' => 'Toor Dal']);

    $search = fn ($term) => $this->getJson('/admin/products?' . http_build_query([
        'draw' => 1, 'start' => 0, 'length' => 10, 'search' => ['value' => $term, 'regex' => 'false'],
        'columns' => [
            ['data' => 'code', 'name' => 'code', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
        ],
    ]), $ajax)->assertOk()->json('data');

    expect(collect($search('d01'))->pluck('code')->all())->toBe(['D01'])
        ->and(collect($search('masoori'))->pluck('code')->all())->toBe(['R01']);
});

test('search scope matches code or name and escapes wildcards', function () {
    makeProduct($this);
    makeProduct($this, ['code' => 'D01', 'name' => 'Toor Dal']);

    expect(Product::search('r0')->pluck('code')->all())->toBe(['R01'])
        ->and(Product::search('dal')->pluck('code')->all())->toBe(['D01'])
        ->and(Product::search('%')->count())->toBe(0)
        ->and(Product::search('')->count())->toBe(2);
});

// Masters used by products (Module 3 guard)
test('category, tax and unit used by a product cannot be deleted', function () {
    $this->actingAs(User::factory()->admin()->create());
    $product = makeProduct($this);
    $product->quickQtys()->create(['unit_id' => $this->g->id, 'qty' => 250, 'sort_order' => 1]);

    $this->delete("/admin/categories/{$this->spices->id}")
        ->assertSessionHas('danger', 'Category cannot be deleted because it is used by products. Mark it inactive instead.');
    $this->delete("/admin/taxes/{$this->gst5->id}")
        ->assertSessionHas('danger', 'Tax cannot be deleted because it is used by products. Mark it inactive instead.');
    $this->delete("/admin/units/{$this->g->id}")
        ->assertSessionHas('danger', 'Unit cannot be deleted because it is used by product quick quantities.');

    expect(Category::whereKey($this->spices->id)->exists())->toBeTrue()
        ->and(Tax::whereKey($this->gst5->id)->exists())->toBeTrue()
        ->and(Unit::whereKey($this->g->id)->exists())->toBeTrue();
});

test('unit used by products keeps its base unit and factor but other fields can change', function () {
    $this->actingAs(User::factory()->admin()->create());
    $product = makeProduct($this);
    $product->quickQtys()->create(['unit_id' => $this->g->id, 'qty' => 250, 'sort_order' => 1]);

    $message = 'This unit is used by products, so its base unit and conversion factor cannot be changed.';

    $this->put("/admin/units/{$this->g->id}", ['name' => 'Gram', 'short_name' => 'g', 'base_unit_id' => $this->kg->id, 'conversion_factor' => '0.01'])
        ->assertSessionHasErrors(['conversion_factor' => $message]);
    $this->put("/admin/units/{$this->g->id}", ['name' => 'Gram', 'short_name' => 'g', 'base_unit_id' => '', 'conversion_factor' => ''])
        ->assertSessionHasErrors(['base_unit_id' => $message]);
    $this->put("/admin/units/{$this->pcs->id}", ['name' => 'Pieces', 'short_name' => 'pcs', 'base_unit_id' => '', 'conversion_factor' => ''])
        ->assertSessionHasNoErrors();

    // Same base and factor: renaming is fine.
    $this->put("/admin/units/{$this->g->id}", ['name' => 'Grams', 'short_name' => 'g', 'base_unit_id' => $this->kg->id, 'conversion_factor' => '0.001'])
        ->assertSessionHasNoErrors();
    expect($this->g->fresh())->name->toBe('Grams')->conversion_factor->toBe('0.001000');
});

test('product permissions are seeded for admin only', function () {
    $staff = User::factory()->create();

    foreach (['list', 'create', 'edit', 'delete'] as $action) {
        expect(Role::findByName(User::$admin)->hasPermissionTo("product.{$action}"))->toBeTrue()
            ->and($staff->can("product.{$action}"))->toBeFalse();
    }
});
