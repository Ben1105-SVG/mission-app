<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', function (User $user) {
            return $user->isAdmin() || $user->isModerator();
        });

        Gate::define('manage-inquiries', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-questions', function (User $user) {
            return $user->isAdmin() || $user->isModerator();
        });

        // Set default pagination view
        \Illuminate\Pagination\Paginator::defaultView('pagination::tailwind');
        \Illuminate\Pagination\Paginator::defaultSimpleView('pagination::simple-tailwind');
    }
}
