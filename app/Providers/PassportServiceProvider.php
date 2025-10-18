<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider as BasePassportServiceProvider;
use App\Bridge\ClientRepository;

class PassportServiceProvider extends BasePassportServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Override the client repository to handle plain text secrets
        $this->app->bind(
            \Laravel\Passport\Bridge\ClientRepository::class,
            ClientRepository::class
        );
    }
}
