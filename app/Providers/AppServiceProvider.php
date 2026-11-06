<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            // Admin can do everything (requirements §1); Staff relies on spatie permissions.
            return $user->isAdmin() && $user->is_active ? true : null;
        });

        // Admin-only areas (user and role management) regardless of spatie permissions.
        Gate::define('admin-only', fn ($user) => $user->isAdmin());

        Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->can($permission);
        });
        
        Blade::anonymousComponentPath(resource_path('views/backend/components'), 'backend');
    }
}
