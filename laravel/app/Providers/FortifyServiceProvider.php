<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Fortify::loginView(fn () => abort(404));
        // Fortify::registerView(fn () => abort(404));
        // Fortify::requestPasswordResetLinkView(fn () => abort(404));
        // Fortify::resetPasswordView(fn () => abort(404));
        // Fortify::verifyEmailView(fn () => abort(404));
        Fortify::ignoreRoutes();

    }
}
