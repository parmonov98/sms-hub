<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class HorizonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Authorize Horizon dashboard for authenticated Filament users (web guard by default)
        Horizon::auth(function ($request) {
            $guard = config('filament.auth.guard', 'web');
            return auth()->guard($guard)->check();
        });
    }
}


