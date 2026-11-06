<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Shop Admin users: login by username, role admin|staff, is_active flag.
 * Source: database-structure.xlsx → users sheet (rows 5–13).
 *
 * Existing data: status → is_active; users holding the spatie "Super Admin"/"Admin"
 * role become admin; users holding the old "Customer" role become disabled staff
 * (shop customers move to their own table in the Customers module).
 *
 * down() restores the status column but cannot re-enable former Customer logins and
 * leaves email nullable; rollback is intended for development only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->after('name');
            $table->enum('role', ['admin', 'staff'])->default('staff')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });

        $roleOf = fn (int $userId) => DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $userId)
            ->pluck('roles.name')->all();

        foreach (DB::table('users')->get() as $user) {
            $roles = Schema::hasTable('model_has_roles') ? $roleOf($user->id) : [];
            $isAdmin = (bool) array_intersect(['Super Admin', 'Admin'], $roles);
            $isCustomer = in_array('Customer', $roles, true) && ! $isAdmin;

            DB::table('users')->where('id', $user->id)->update([
                'username' => substr(preg_replace('/[^A-Za-z0-9._-]/', '', strstr((string) $user->email, '@', true) ?: '') ?: 'user', 0, 40) . $user->id,
                'role' => $isAdmin ? 'admin' : 'staff',
                'is_active' => ! $isCustomer && (int) $user->status === 1,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable(false)->unique()->change();
            $table->string('email', 150)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        DB::table('users')->update(['status' => DB::raw('is_active')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'role', 'is_active']);
        });
    }
};
