<?php

namespace App\Providers;

use App\Integrations\Petstore\PetstoreClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PetstoreClient::class, function () {
            return new PetstoreClient(
                baseUrl: (string) config('services.petstore.base_url'),
                timeout: (int) config('services.petstore.timeout', 10),
                retries: (int) config('services.petstore.retries', 2),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
