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
            return $user->hasRole(User::$superAdmin) ? true : null;
        });

        Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->can($permission);
        });
        
        Blade::anonymousComponentPath(resource_path('views/backend/components'), 'backend');
    }
}
