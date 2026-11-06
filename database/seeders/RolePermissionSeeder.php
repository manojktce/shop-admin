<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Two roles (requirements §1, §3.1):
 *  Admin – everything (also granted by Gate::before for users.role = admin).
 *  Staff – billing, customer receipts, petty expenses, stock view. Each module adds
 *          its permissions to $permissions with the subset granted to Staff.
 */
class RolePermissionSeeder extends Seeder
{
    /** [group, permissions, permissions also granted to Staff] */
    public static array $permissions = [
        ['Users', ['user.list', 'user.create', 'user.edit'], []],
        ['Roles', ['role.list', 'role.create', 'role.edit', 'role.delete'], []],
        ['Settings', ['setting.edit'], []],
        ['Categories', ['category.list', 'category.create', 'category.edit', 'category.delete'], []],
        ['Units', ['unit.list', 'unit.create', 'unit.edit', 'unit.delete'], []],
        ['Taxes', ['tax.list', 'tax.create', 'tax.edit', 'tax.delete'], []],
        ['Payment modes', ['payment_mode.list', 'payment_mode.create', 'payment_mode.edit', 'payment_mode.delete'], []],
        ['Expense categories', ['expense_category.list', 'expense_category.create', 'expense_category.edit', 'expense_category.delete'], []],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => User::$admin]);
        $staff = Role::firstOrCreate(['name' => User::$staff]);

        foreach (static::$permissions as [$group, $names, $staffNames]) {
            foreach ($names as $name) {
                $permission = Permission::firstOrCreate(['name' => $name], ['group_name' => $group]);
                $admin->givePermissionTo($permission);

                if (in_array($name, $staffNames, true)) {
                    $staff->givePermissionTo($permission);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
