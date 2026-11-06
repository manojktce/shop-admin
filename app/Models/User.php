<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'mobile', 'dob', 'gender', 'image', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasRoles;

    public static $gender = ['1' => 'Male', '2' => 'Female', '3' => 'Other'];

    public static $superAdmin = 'Super Admin';
    public static $admin = 'Admin';
    public static $customer = 'Customer';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'status' => 'boolean',
        ];
    }

    public function scopeAdmins($query)
    {
        return $query->role(self::$admin);
    }

    public function scopeCustomers($query)
    {
        return $query->role(self::$customer);
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
