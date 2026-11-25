<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Mcp\Tools\GetAlertDetailsTool;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Mcp\Server;

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
      Mcp::local('banking', BankingServer::class);
    }
}