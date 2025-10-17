<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\Display;

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
        // Bind {display} route parameter to Display model by `token` instead of `id`.
        Route::bind('displayToken', function ($value) {
            return Display::where('token', $value)->firstOrFail();
        });
    }
}
