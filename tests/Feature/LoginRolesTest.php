<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

// R-01: login by username
test('active user logs in with username and password', function () {
    User::factory()->create(['username' => 'priya', 'password' => 'secret123']);

    $this->post('/admin', ['username' => 'priya', 'password' => 'secret123'])
        ->assertRedirect(url('admin/dashboard'));

    $this->assertAuthenticated();
});

test('login rejects wrong password and missing username', function () {
    User::factory()->create(['username' => 'priya', 'password' => 'secret123']);

    $this->post('/admin', ['username' => 'priya', 'password' => 'wrong'])->assertSessionHas('danger', 'Invalid Credentials.');
    $this->post('/admin', ['password' => 'secret123'])->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('disabled user cannot log in', function () {
    User::factory()->disabled()->create(['username' => 'karthik', 'password' => 'secret123']);

    $this->post('/admin', ['username' => 'karthik', 'password' => 'secret123'])
        ->assertSessionHas('danger', 'Your account is not active.');

    $this->assertGuest();
});

test('user disabled after login is signed out on next request', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $user->update(['is_active' => false]);

    $this->get('/admin/dashboard')->assertRedirect(url('admin'));
    $this->assertGuest();
});

// R-02: roles
test('users.role is mirrored to the spatie role', function () {
    $user = User::factory()->create();
    expect($user->getRoleNames()->all())->toBe([User::$staff]);

    $user->update(['role' => User::ROLE_ADMIN]);
    expect($user->fresh()->getRoleNames()->all())->toBe([User::$admin]);
});

test('admin passes every permission check, staff does not get admin-only permissions', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->create();

    expect($admin->can('user.create'))->toBeTrue()
        ->and($admin->can('anything.at.all'))->toBeTrue()
        ->and($staff->can('user.create'))->toBeFalse();
});

test('guest is redirected from protected pages', function () {
    $this->get('/admin/users')->assertRedirect(url('admin'));
});

// R-03: user management
test('staff cannot open user management', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/users/create')->assertForbidden();
    $this->post('/admin/users', [])->assertForbidden();
});

test('admin sees the user list and data', function () {
    $this->actingAs(User::factory()->admin()->create());
    User::factory()->create(['username' => 'priya']);

    $this->get('/admin/users')->assertOk()->assertSee('Users');
    $this->getJson('/admin/users', ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()->assertJsonFragment(['username' => 'priya']);
});

test('admin creates a staff user', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/users', [
        'name' => 'Priya R', 'username' => 'priya', 'email' => '', 'password' => 'secret123', 'role' => 'staff', 'is_active' => '1',
    ])->assertRedirect(url('admin/users'))->assertSessionHas('success');

    $user = User::where('username', 'priya')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('staff')
        ->and($user->is_active)->toBeTrue()
        ->and($user->email)->toBeNull()
        ->and(Hash::check('secret123', $user->password))->toBeTrue()
        ->and($user->hasRole(User::$staff))->toBeTrue();
});

test('user creation validates input', function () {
    $this->actingAs(User::factory()->admin()->create());
    User::factory()->create(['username' => 'priya']);

    $this->post('/admin/users', ['username' => 'priya', 'role' => 'owner', 'password' => '1'])
        ->assertSessionHasErrors(['name', 'username', 'role', 'password']);
});

test('admin disables a user and resets the password', function () {
    $this->actingAs(User::factory()->admin()->create());
    $user = User::factory()->create(['username' => 'karthik', 'password' => 'oldpass1']);

    $this->put("/admin/users/{$user->id}", [
        'name' => $user->name, 'username' => 'karthik', 'password' => 'newpass1', 'role' => 'staff',
    ])->assertRedirect(url('admin/users'));

    $user->refresh();
    expect($user->is_active)->toBeFalse()
        ->and(Hash::check('newpass1', $user->password))->toBeTrue();
});

test('blank password on edit keeps the current password', function () {
    $this->actingAs(User::factory()->admin()->create());
    $user = User::factory()->create(['password' => 'keepme12']);

    $this->put("/admin/users/{$user->id}", [
        'name' => 'New Name', 'username' => $user->username, 'password' => '', 'role' => 'staff', 'is_active' => '1',
    ])->assertSessionHasNoErrors()->assertSessionHas('success');

    expect(Hash::check('keepme12', $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->name)->toBe('New Name');
});

test('admin cannot disable or demote themselves', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->put("/admin/users/{$admin->id}", [
        'name' => $admin->name, 'username' => $admin->username, 'role' => 'staff',
    ]);

    $admin->refresh();
    expect($admin->is_active)->toBeTrue()->and($admin->role)->toBe('admin');
});

test('profile update cannot change own role or active flag', function () {
    $staff = User::factory()->create(['name' => 'Priya']);
    $this->actingAs($staff);

    $this->put('/admin/profile', ['name' => 'Priya R', 'role' => 'admin', 'is_active' => '0', 'username' => 'hacker'])
        ->assertSessionHas('success');

    $staff->refresh();
    expect($staff->name)->toBe('Priya R')
        ->and($staff->role)->toBe('staff')
        ->and($staff->is_active)->toBeTrue()
        ->and($staff->username)->not->toBe('hacker');
});

test('users table has username, role and is_active and no legacy status column', function () {
    expect(\Illuminate\Support\Facades\Schema::hasColumns('users', ['username', 'role', 'is_active']))->toBeTrue()
        ->and(\Illuminate\Support\Facades\Schema::hasColumn('users', 'status'))->toBeFalse();
});

// Security review fixes
test('staff with role/user permissions still cannot reach user or role management', function () {
    $staff = User::factory()->create();
    $staff->givePermissionTo(['user.list', 'user.edit', 'role.edit']);
    $this->actingAs($staff);

    $this->get('/admin/users')->assertForbidden();
    $admin = User::factory()->admin()->create();
    $this->put("/admin/users/{$admin->id}", ['name' => 'x', 'username' => 'x', 'role' => 'staff'])->assertForbidden();
    expect($admin->fresh()->role)->toBe('admin');
});

test('built-in Admin role cannot be edited and Staff role cannot be renamed or deleted', function () {
    $this->actingAs(User::factory()->admin()->create());
    $adminRole = \Spatie\Permission\Models\Role::findByName(User::$admin);
    $staffRole = \Spatie\Permission\Models\Role::findByName(User::$staff);

    $this->get("/admin/roles/{$adminRole->id}/edit")->assertNotFound();
    $this->put("/admin/roles/{$staffRole->id}", ['name' => 'Renamed', 'guard_name' => 'api']);
    expect($staffRole->fresh()->name)->toBe(User::$staff)->and($staffRole->fresh()->guard_name)->toBe('web');

    $this->delete("/admin/roles/{$staffRole->id}");
    expect(\Spatie\Permission\Models\Role::where('name', User::$staff)->exists())->toBeTrue();
});

test('login is throttled after repeated attempts', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->post('/admin', ['username' => 'nobody', 'password' => 'x']);
    }

    $this->post('/admin', ['username' => 'nobody', 'password' => 'x'])->assertStatus(429);
});

test('saving a user recreates a missing mirrored spatie role instead of failing', function () {
    \Spatie\Permission\Models\Role::where('name', User::$staff)->delete();
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    $user = User::factory()->create();

    expect($user->hasRole(User::$staff))->toBeTrue();
});
