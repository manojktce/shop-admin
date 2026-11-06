<?php

use App\Models\ShopSetting;
use App\Models\User;
use App\Services\NumberSequence;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ShopSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->seed([RolePermissionSeeder::class, ShopSettingSeeder::class]);
});

function validSettings(array $overrides = []): array
{
    return array_merge([
        'shop_name' => 'Sri Lakshmi Stores',
        'address' => '12, Main Road',
        'phone' => '9840012345',
        'gstin' => '33ABCDE1234F1Z5',
        'invoice_prefix' => 'INV-2026-',
        'invoice_next_no' => '1',
        'receipt_prefix' => 'RCP-',
        'receipt_next_no' => '1',
        'stock_entry_prefix' => 'SIN-',
        'stock_entry_next_no' => '1',
        'print_size' => 'thermal_80mm',
        'bill_rounding' => 'nearest_rupee',
        'allow_negative_stock' => '1',
    ], $overrides);
}

// R-04: settings saved and read back
test('admin opens and saves shop settings', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    $this->get('/admin/settings')->assertOk()->assertSee('Shop settings');

    $this->put('/admin/settings', validSettings(['logo' => UploadedFile::fake()->image('logo.png')]))
        ->assertRedirect(url('admin/settings'))->assertSessionHas('success');

    expect(ShopSetting::get('shop_name'))->toBe('Sri Lakshmi Stores')
        ->and(ShopSetting::get('gstin'))->toBe('33ABCDE1234F1Z5')
        ->and(ShopSetting::get('invoice_next_no'))->toBe('1')
        ->and(ShopSetting::get('allow_negative_stock'))->toBe('1');

    Storage::disk('public')->assertExists(ShopSetting::get('logo'));
});

test('unchecked negative-stock switch saves 0 and logo is kept when not re-uploaded', function () {
    $this->actingAs(User::factory()->admin()->create());
    ShopSetting::put('logo', 'shop/old.png');

    $this->put('/admin/settings', validSettings(['allow_negative_stock' => null]));

    expect(ShopSetting::get('allow_negative_stock'))->toBe('0')
        ->and(ShopSetting::get('logo'))->toBe('shop/old.png');
});

test('settings are validated', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->put('/admin/settings', validSettings([
        'shop_name' => '', 'gstin' => 'BAD', 'invoice_next_no' => '0', 'print_size' => 'a5', 'bill_rounding' => 'up',
    ]))->assertSessionHasErrors(['shop_name', 'gstin', 'invoice_next_no', 'print_size', 'bill_rounding']);
});

test('logo must be an image', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->put('/admin/settings', validSettings(['logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')]))
        ->assertSessionHasErrors('logo');
});

test('staff and guests cannot change settings', function () {
    $this->get('/admin/settings')->assertRedirect(url('admin'));

    $this->actingAs(User::factory()->create());
    $this->get('/admin/settings')->assertForbidden();
    $this->put('/admin/settings', validSettings())->assertForbidden();
    expect(ShopSetting::get('shop_name'))->toBe('My Shop');
});

test('defaults are used for settings not yet saved', function () {
    ShopSetting::query()->delete();
    ShopSetting::forgetCache();

    expect(ShopSetting::get('print_size'))->toBe('thermal_80mm')
        ->and(ShopSetting::get('bill_rounding'))->toBe('nearest_rupee');
});

// BR-03: invoice numbers continue automatically
test('number sequence formats and continues from the saved counter', function () {
    ShopSetting::put('invoice_prefix', 'INV-2026-');
    ShopSetting::put('invoice_next_no', '47');

    expect(NumberSequence::next(NumberSequence::INVOICE))->toBe('INV-2026-0047')
        ->and(NumberSequence::next(NumberSequence::INVOICE))->toBe('INV-2026-0048')
        ->and(ShopSetting::get('invoice_next_no'))->toBe('49');
});

test('separate sequences for receipts and stock in', function () {
    expect(NumberSequence::next(NumberSequence::RECEIPT))->toBe('RCP-0001')
        ->and(NumberSequence::next(NumberSequence::STOCK_ENTRY))->toBe('SIN-0001')
        ->and(NumberSequence::next(NumberSequence::RECEIPT))->toBe('RCP-0002');
});

test('sequence creates a missing counter row', function () {
    ShopSetting::where('key', 'receipt_next_no')->delete();
    ShopSetting::forgetCache();

    expect(NumberSequence::next(NumberSequence::RECEIPT))->toBe('RCP-0001');
});

// Review fixes
test('saving the form with a stale counter does not move numbering backwards', function () {
    $this->actingAs(User::factory()->admin()->create());
    ShopSetting::put('invoice_next_no', '50');
    foreach (range(1, 10) as $i) {
        NumberSequence::next(NumberSequence::INVOICE); // billing continued: counter now 60
    }

    // Unchanged-from-current values are fine; a lower value is rejected.
    $this->put('/admin/settings', validSettings(['invoice_next_no' => '50']))->assertSessionHasErrors('invoice_next_no');
    expect(ShopSetting::get('invoice_next_no'))->toBe('60');

    $this->put('/admin/settings', validSettings(['invoice_next_no' => '60', 'receipt_next_no' => '1', 'phone' => '111']))
        ->assertSessionHasNoErrors();
    expect(ShopSetting::get('phone'))->toBe('111')->and(ShopSetting::get('invoice_next_no'))->toBe('60');

    $this->put('/admin/settings', validSettings(['invoice_next_no' => '100', 'receipt_next_no' => '1']))->assertSessionHasNoErrors();
    expect(NumberSequence::next(NumberSequence::INVOICE))->toBe('INV-2026-0100');
});

test('counter values are never served stale from the cache', function () {
    ShopSetting::get('shop_name'); // warm cache
    NumberSequence::next(NumberSequence::INVOICE);

    expect(ShopSetting::get('invoice_next_no'))->toBe('2');
});

test('lowercase GSTIN is accepted and stored uppercase', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->put('/admin/settings', validSettings(['gstin' => '33abcde1234f1z5', 'invoice_next_no' => '1', 'receipt_next_no' => '1']))
        ->assertSessionHasNoErrors();

    expect(ShopSetting::get('gstin'))->toBe('33ABCDE1234F1Z5');
});

test('staff granted setting.edit still cannot change settings', function () {
    $staff = User::factory()->create();
    $staff->givePermissionTo('setting.edit');

    $this->actingAs($staff)->get('/admin/settings')->assertForbidden();
});
