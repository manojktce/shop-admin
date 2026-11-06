<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::$superAdmin;
        $admin = User::$admin;
        $customer = User::$customer;
        
        $roles = [
            $superAdmin,
            $admin,
            $customer,
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $permissions = [
            ['Customers', ['customer.list', 'customer.create', 'customer.edit', 'customer.delete'], [$admin]],
            ['Roles', ['role.list', 'role.create', 'role.edit', 'role.delete'], [$admin]],
        ];

        foreach ($permissions as $perm) {
            foreach ($perm[1] as $permissionName) {
                $permission = Permission::firstOrCreate(['group_name' => $perm[0], 'name' => $permissionName]);

                if (isset($perm[2])) {
                    foreach ($perm[2] as $roleName) {
                        $role = Role::where('name', $roleName)->first();
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }
    }
}
