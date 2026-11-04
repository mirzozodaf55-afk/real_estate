<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // POST /api/broadcasting/auth — сайт подтверждает подписку на приватный канал токеном Sanctum (как остальной API)
        Broadcast::routes(['prefix' => 'api', 'middleware' => ['auth:sanctum', 'not.blocked']]);

        require base_path('routes/channels.php');
    }
}
