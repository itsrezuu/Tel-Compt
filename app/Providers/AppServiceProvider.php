<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        View::composer(
            ['layouts.mahasiswa', 'layouts.admin-hima', 'layouts.dosen'],
            function (\Illuminate\View\View $view): void {
                $user = Auth::user();
                $view->with('unreadNotifications', $user?->unreadNotifications()->count() ?? 0);
            },
        );
    }
}
