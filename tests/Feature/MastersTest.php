<?php

use App\Models\Category;
use App\Models\ExpenseCategory;
use App\Models\PaymentMode;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\MasterSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

function actingAsAdmin($test)
{
    return $test->actingAs(User::factory()->admin()->create());
}

$ajax = ['X-Requested-With' => 'XMLHttpRequest'];

// R-05: access – masters are Admin only (DOC §1)
test('staff and guests cannot reach any master screen', function (string $path) {
    $this->get("/admin/{$path}")->assertRedirect();

    $this->actingAs(User::factory()->create());
    $this->get("/admin/{$path}")->assertForbidden();
    $this->get("/admin/{$path}/create")->assertForbidden();
    $this->post("/admin/{$path}", ['name' => 'X'])->assertForbidden();
})->with(['categories', 'units', 'taxes', 'payment-modes', 'expense-categories']);

test('staff granted master permissions still cannot change masters', function () {
    Role::findByName(User::$staff)->givePermissionTo(['category.list', 'category.create', 'category.delete']);
    $this->actingAs(User::factory()->create());
    $category = Category::create(['name' => 'Oils', 'sort_order' => 1, 'is_active' => true]);

    $this->get('/admin/categories')->assertForbidden();
    $this->post('/admin/categories', ['name' => 'Soaps', 'sort_order' => 2])->assertForbidden();
    $this->delete("/admin/categories/{$category->id}")->assertForbidden();
    expect(Category::count())->toBe(1);
});

test('admin opens every master list, data feed and create form', function (string $path, string $title) use ($ajax) {
    actingAsAdmin($this);

    $this->get("/admin/{$path}")->assertOk()->assertSee($title);
    $this->getJson("/admin/{$path}", $ajax)->assertOk()->assertJsonStructure(['data']);
    $this->get("/admin/{$path}/create")->assertOk();
})->with([
    ['categories', 'Categories'],
    ['units', 'Units'],
    ['taxes', 'Tax (GST)'],
    ['payment-modes', 'Payment modes'],
    ['expense-categories', 'Expense categories'],
]);

// R-05: categories
test('admin creates, edits and deletes a category', function () use ($ajax) {
    actingAsAdmin($this);

    $this->post('/admin/categories', ['name' => 'Rice & grains', 'sort_order' => '1', 'is_active' => '1'])
        ->assertRedirect(url('admin/categories'))->assertSessionHas('success');
    $category = Category::firstWhere('name', 'Rice & grains');
    expect($category->sort_order)->toBe(1)->and($category->is_active)->toBeTrue();

    $this->getJson('/admin/categories', $ajax)->assertJsonFragment(['name' => 'Rice &amp; grains']);
    $this->get("/admin/categories/{$category->id}/edit")->assertOk()->assertSee('Rice &amp; grains', false);

    // Unchecked switch = inactive.
    $this->put("/admin/categories/{$category->id}", ['name' => 'Rice', 'sort_order' => '3'])
        ->assertRedirect(url('admin/categories'));
    expect($category->fresh())->name->toBe('Rice')->sort_order->toBe(3)->is_active->toBeFalse();

    $this->delete("/admin/categories/{$category->id}")->assertRedirect(url('admin/categories'))->assertSessionHas('success');
    expect(Category::count())->toBe(0);
});

test('category is validated', function () {
    actingAsAdmin($this);
    Category::create(['name' => 'Oils', 'sort_order' => 1, 'is_active' => true]);

    $this->post('/admin/categories', ['name' => '', 'sort_order' => '-1'])->assertSessionHasErrors(['name', 'sort_order']);
    $this->post('/admin/categories', ['name' => 'Oils', 'sort_order' => 'x'])->assertSessionHasErrors(['name', 'sort_order']);
    $this->post('/admin/categories', ['name' => str_repeat('a', 101), 'sort_order' => 1])->assertSessionHasErrors('name');
    expect(Category::count())->toBe(1);
});

test('editing a category keeps its own name valid', function () {
    actingAsAdmin($this);
    $category = Category::create(['name' => 'Oils', 'sort_order' => 1, 'is_active' => true]);

    $this->put("/admin/categories/{$category->id}", ['name' => 'Oils', 'sort_order' => 5, 'is_active' => 1])
        ->assertSessionHasNoErrors();
    expect($category->fresh()->sort_order)->toBe(5);
});

test('editing or deleting a missing master returns 404', function () {
    actingAsAdmin($this);

    $this->get('/admin/categories/999/edit')->assertNotFound();
    $this->put('/admin/taxes/999', ['name' => 'GST', 'rate' => 5])->assertNotFound();
    $this->delete('/admin/units/999')->assertNotFound();
});

// R-06: units and conversion
test('base unit is saved with factor 1 and no base unit even if a factor is posted', function () {
    actingAsAdmin($this);

    $this->post('/admin/units', ['name' => 'Kilogram', 'short_name' => 'kg', 'base_unit_id' => '', 'conversion_factor' => '5'])
        ->assertSessionHasNoErrors()->assertRedirect(url('admin/units'));

    $kg = Unit::firstWhere('short_name', 'kg');
    expect($kg->isBase())->toBeTrue()
        ->and((float) $kg->conversion_factor)->toBe(1.0)
        ->and($kg->baseUnitId())->toBe($kg->id);
});

test('derived unit converts to its base unit', function () use ($ajax) {
    actingAsAdmin($this);
    $kg = Unit::create(['name' => 'Kilogram', 'short_name' => 'kg', 'conversion_factor' => 1]);

    $this->post('/admin/units', ['name' => 'Quintal', 'short_name' => 'qtl', 'base_unit_id' => $kg->id, 'conversion_factor' => '100'])
        ->assertSessionHasNoErrors();
    $this->post('/admin/units', ['name' => 'Gram', 'short_name' => 'g', 'base_unit_id' => $kg->id, 'conversion_factor' => '0.001'])
        ->assertSessionHasNoErrors();

    $qtl = Unit::firstWhere('short_name', 'qtl');
    $g = Unit::firstWhere('short_name', 'g');

    // DOC §4.2: "1 quintal" or "100 kg" both add 100 kg to stock.
    expect($qtl->toBase(1))->toBe(100.0)
        ->and($kg->toBase(100))->toBe(100.0)
        ->and($g->toBase(50))->toBe(0.05)
        ->and($qtl->baseUnitId())->toBe($kg->id)
        ->and($kg->derivedUnits()->count())->toBe(2);

    $this->getJson('/admin/units', $ajax)->assertJsonFragment(['meaning' => '1 qtl = 100 kg'])
        ->assertJsonFragment(['meaning' => '1 g = 0.001 kg']);
});

test('unit is validated', function () {
    actingAsAdmin($this);
    $kg = Unit::create(['name' => 'Kilogram', 'short_name' => 'kg', 'conversion_factor' => 1]);
    $g = Unit::create(['name' => 'Gram', 'short_name' => 'g', 'base_unit_id' => $kg->id, 'conversion_factor' => 0.001]);

    // Missing fields, duplicate short name.
    $this->post('/admin/units', ['name' => '', 'short_name' => 'kg'])->assertSessionHasErrors(['name', 'short_name']);
    // Derived unit needs a positive factor.
    $this->post('/admin/units', ['name' => 'Box', 'short_name' => 'box', 'base_unit_id' => $kg->id, 'conversion_factor' => ''])
        ->assertSessionHasErrors('conversion_factor');
    $this->post('/admin/units', ['name' => 'Box', 'short_name' => 'box', 'base_unit_id' => $kg->id, 'conversion_factor' => '0'])
        ->assertSessionHasErrors('conversion_factor');
    // More than 6 decimals would be rounded to 0 by DECIMAL(12,6).
    $this->post('/admin/units', ['name' => 'Box', 'short_name' => 'box', 'base_unit_id' => $kg->id, 'conversion_factor' => '0.0000001'])
        ->assertSessionHasErrors('conversion_factor');
    // Base must itself be a base unit (no chains) and must exist.
    $this->post('/admin/units', ['name' => 'Milligram', 'short_name' => 'mg', 'base_unit_id' => $g->id, 'conversion_factor' => '0.001'])
        ->assertSessionHasErrors('base_unit_id');
    $this->post('/admin/units', ['name' => 'Box', 'short_name' => 'box', 'base_unit_id' => 999, 'conversion_factor' => '12'])
        ->assertSessionHasErrors('base_unit_id');

    expect(Unit::count())->toBe(2);
});

test('a unit cannot be based on itself and a unit others are based on stays a base unit', function () {
    actingAsAdmin($this);
    $kg = Unit::create(['name' => 'Kilogram', 'short_name' => 'kg', 'conversion_factor' => 1]);
    $litre = Unit::create(['name' => 'Litre', 'short_name' => 'litre', 'conversion_factor' => 1]);
    Unit::create(['name' => 'Gram', 'short_name' => 'g', 'base_unit_id' => $kg->id, 'conversion_factor' => 0.001]);

    $this->put("/admin/units/{$litre->id}", ['name' => 'Litre', 'short_name' => 'litre', 'base_unit_id' => $litre->id, 'conversion_factor' => 2])
        ->assertSessionHasErrors('base_unit_id');
    $this->put("/admin/units/{$kg->id}", ['name' => 'Kilogram', 'short_name' => 'kg', 'base_unit_id' => $litre->id, 'conversion_factor' => 2])
        ->assertSessionHasErrors('base_unit_id');

    expect($kg->fresh()->isBase())->toBeTrue()->and($litre->fresh()->isBase())->toBeTrue();
});

test('derived unit can become a base unit again and its factor resets to 1', function () {
    actingAsAdmin($this);
    $pcs = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'conversion_factor' => 1]);
    $box = Unit::create(['name' => 'Box (12)', 'short_name' => 'box', 'base_unit_id' => $pcs->id, 'conversion_factor' => 12]);

    $this->get("/admin/units/{$box->id}/edit")->assertOk()->assertSee('Pieces (pcs)');

    $this->put("/admin/units/{$box->id}", ['name' => 'Box', 'short_name' => 'box', 'base_unit_id' => '', 'conversion_factor' => '12'])
        ->assertSessionHasNoErrors();

    expect($box->fresh()->isBase())->toBeTrue()->and((float) $box->fresh()->conversion_factor)->toBe(1.0);
});

test('unit edit form does not offer the unit itself as its base', function () {
    actingAsAdmin($this);
    $kg = Unit::create(['name' => 'Kilogram', 'short_name' => 'kg', 'conversion_factor' => 1]);
    Unit::create(['name' => 'Litre', 'short_name' => 'litre', 'conversion_factor' => 1]);

    $this->get("/admin/units/{$kg->id}/edit")->assertOk()
        ->assertSee('Litre (litre)')
        ->assertDontSee('Kilogram (kg)');
});

// R-05 / R-06: delete guard
test('a base unit used by other units cannot be deleted', function () {
    actingAsAdmin($this);
    $kg = Unit::create(['name' => 'Kilogram', 'short_name' => 'kg', 'conversion_factor' => 1]);
    $g = Unit::create(['name' => 'Gram', 'short_name' => 'g', 'base_unit_id' => $kg->id, 'conversion_factor' => 0.001]);

    $this->from('/admin/units')->delete("/admin/units/{$kg->id}")
        ->assertRedirect('/admin/units')
        ->assertSessionHas('danger', 'Unit cannot be deleted because it is used by other units.');
    expect(Unit::count())->toBe(2);

    // The derived unit is free to delete, after which the base unit is too.
    $this->delete("/admin/units/{$g->id}")->assertSessionHas('success');
    $this->delete("/admin/units/{$kg->id}")->assertSessionHas('success');
    expect(Unit::count())->toBe(0);
});

test('in-use masters with a status cannot be deleted and suggest marking them inactive', function () {
    actingAsAdmin($this);
    $category = Category::create(['name' => 'Oils', 'sort_order' => 1, 'is_active' => true]);

    // Simulate the Products module registering a usage relation on categories.
    app()->bind(Category::class, fn () => new class extends Category {
        protected $table = 'categories';
        protected static array $usageRelations = ['inUse' => 'products'];

        public function inUse() { return $this->hasMany(Category::class, 'id', 'id'); }
    });

    $this->from('/admin/categories')->delete("/admin/categories/{$category->id}")
        ->assertRedirect('/admin/categories')
        ->assertSessionHas('danger', 'Category cannot be deleted because it is used by products. Mark it inactive instead.');
    expect(Category::count())->toBe(1);
});

// R-05: taxes
test('admin creates and edits a tax slab', function () {
    actingAsAdmin($this);

    $this->post('/admin/taxes', ['name' => 'GST 5%', 'rate' => '5', 'is_active' => '1'])->assertSessionHasNoErrors();
    $tax = Tax::firstWhere('name', 'GST 5%');
    expect($tax->rate)->toBe('5.00')->and($tax->is_active)->toBeTrue();

    $this->put("/admin/taxes/{$tax->id}", ['name' => 'GST 12%', 'rate' => '12.5'])->assertSessionHasNoErrors();
    expect($tax->fresh())->name->toBe('GST 12%')->rate->toBe('12.50')->is_active->toBeFalse();
});

test('tax rate is validated', function (array $input) {
    actingAsAdmin($this);

    $this->post('/admin/taxes', array_merge(['name' => 'GST', 'rate' => '5'], $input))->assertSessionHasErrors(array_keys($input));
    expect(Tax::count())->toBe(0);
})->with([
    'missing name' => [['name' => '']],
    'missing rate' => [['rate' => '']],
    'negative rate' => [['rate' => '-1']],
    'over 100' => [['rate' => '101']],
    'three decimals' => [['rate' => '5.125']],
    'not a number' => [['rate' => 'five']],
]);

// R-05: payment modes
test('admin creates payment modes with drawer flag', function () {
    actingAsAdmin($this);

    $this->post('/admin/payment-modes', ['name' => 'Cash', 'affects_cash_drawer' => '1', 'is_active' => '1'])
        ->assertRedirect(url('admin/payment-modes'))->assertSessionHas('success', 'Payment mode created successfully.');
    $this->post('/admin/payment-modes', ['name' => 'UPI', 'is_active' => '1'])->assertSessionHasNoErrors();

    expect(PaymentMode::firstWhere('name', 'Cash')->affects_cash_drawer)->toBeTrue()
        ->and(PaymentMode::firstWhere('name', 'UPI')->affects_cash_drawer)->toBeFalse();

    $this->post('/admin/payment-modes', ['name' => 'UPI'])->assertSessionHasErrors('name');
    $this->post('/admin/payment-modes', ['name' => ''])->assertSessionHasErrors('name');
    expect(PaymentMode::count())->toBe(2);
});

test('payment mode edit and delete', function () {
    actingAsAdmin($this);
    $card = PaymentMode::create(['name' => 'Card', 'affects_cash_drawer' => false, 'is_active' => true]);

    $this->get("/admin/payment-modes/{$card->id}/edit")->assertOk()->assertSee('Card');
    $this->put("/admin/payment-modes/{$card->id}", ['name' => 'Card', 'affects_cash_drawer' => '1'])
        ->assertRedirect(url('admin/payment-modes'));
    expect($card->fresh())->affects_cash_drawer->toBeTrue()->is_active->toBeFalse();

    $this->delete("/admin/payment-modes/{$card->id}")->assertSessionHas('success', 'Payment mode deleted successfully.');
    expect(PaymentMode::count())->toBe(0);
});

// R-05: expense categories
test('admin manages expense categories', function () use ($ajax) {
    actingAsAdmin($this);

    $this->post('/admin/expense-categories', ['name' => 'Tea & snacks', 'is_active' => '1'])
        ->assertRedirect(url('admin/expense-categories'))->assertSessionHas('success', 'Expense category created successfully.');
    $this->post('/admin/expense-categories', ['name' => 'Tea & snacks'])->assertSessionHasErrors('name');

    $expense = ExpenseCategory::firstWhere('name', 'Tea & snacks');
    $this->getJson('/admin/expense-categories', $ajax)->assertJsonFragment(['name' => 'Tea &amp; snacks']);

    $this->put("/admin/expense-categories/{$expense->id}", ['name' => 'Tea'])->assertSessionHasNoErrors();
    expect($expense->fresh())->name->toBe('Tea')->is_active->toBeFalse();

    $this->delete("/admin/expense-categories/{$expense->id}")->assertSessionHas('success');
    expect(ExpenseCategory::count())->toBe(0);
});

test('list data escapes user-entered names', function () use ($ajax) {
    actingAsAdmin($this);
    Category::create(['name' => '<script>x</script>', 'sort_order' => 1, 'is_active' => true]);

    $this->getJson('/admin/categories', $ajax)->assertOk()
        ->assertJsonFragment(['name' => '&lt;script&gt;x&lt;/script&gt;']);
});

// Starter data
test('master seeder creates the sample masters once and keeps admin edits', function () {
    $this->seed(MasterSeeder::class);

    expect(Unit::count())->toBe(7)
        ->and(Tax::count())->toBe(4)
        ->and(PaymentMode::count())->toBe(4)
        ->and(ExpenseCategory::count())->toBe(4)
        ->and(PaymentMode::firstWhere('name', 'Cash')->affects_cash_drawer)->toBeTrue()
        ->and(Unit::firstWhere('short_name', 'qtl')->toBase(1))->toBe(100.0)
        ->and(Unit::firstWhere('short_name', 'ml')->baseUnit->short_name)->toBe('litre');

    Tax::firstWhere('name', 'GST 5%')->update(['is_active' => false]);
    $this->seed(MasterSeeder::class);

    expect(Unit::count())->toBe(7)
        ->and(Tax::count())->toBe(4)
        ->and(Tax::firstWhere('name', 'GST 5%')->is_active)->toBeFalse();
});

test('master permissions are seeded for admin only', function () {
    $staff = User::factory()->create();
    $admin = User::factory()->admin()->create();

    foreach (['category', 'unit', 'tax', 'payment_mode', 'expense_category'] as $master) {
        foreach (['list', 'create', 'edit', 'delete'] as $action) {
            expect(Role::findByName(User::$admin)->hasPermissionTo("{$master}.{$action}"))->toBeTrue()
                ->and($staff->can("{$master}.{$action}"))->toBeFalse()
                ->and($admin->can("{$master}.{$action}"))->toBeTrue();
        }
    }
});
