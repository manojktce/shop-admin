<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'is_active', 'mobile', 'dob', 'gender', 'image'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';

    public static $gender = ['1' => 'Male', '2' => 'Female', '3' => 'Other'];

    public static $roles = [self::ROLE_ADMIN => 'Admin', self::ROLE_STAFF => 'Staff'];

    /** Spatie role names mirroring users.role (users.role is the source of truth). */
    public static $admin = 'Admin';
    public static $staff = 'Staff';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Keep the spatie role (used by the permission middleware) in step with users.role.
        static::saved(function (User $user) {
            $spatieRole = $user->role === self::ROLE_ADMIN ? self::$admin : self::$staff;

            if ($user->getRoleNames()->all() !== [$spatieRole]) {
                $user->syncRoles([Role::findOrCreate($spatieRole, 'web')]);
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getImageStoragePathAttribute()
    {
        return url("storage/{$this->id}/profile");
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? "{$this->image_storage_path}/{$this->image}" : null;
    }
}
