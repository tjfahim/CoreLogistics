<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // third-party company list for every screen that can assign / filter items by company
        \Illuminate\Support\Facades\View::composer(
            ['admin.cargo_items.*', 'admin.itemshipments.show', 'admin.itemshipments.price'],
            function ($view) {
                try {
                    $view->with('thirdParties', \App\Models\ThirdParty::orderBy('name')->get());
                } catch (\Throwable $e) {   // table not migrated yet
                    $view->with('thirdParties', collect());
                }
            }
        );
    }
}
