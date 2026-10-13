<?php

namespace App\Providers;

use App\Models\RollingText;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Hanya inject ke topbar, jangan ke '*' biar tidak query 20x per halaman
        // + pakai cache 5 menit biar DB tidak kena hit terus
        View::composer(['layouts.partials.topbar', 'components.layout.performance-ticker'], function ($view) {
            $rollingText = Cache::remember('mpnweb_rolling_text', 300, function () {
                return RollingText::latest('id')->first();
            });

            $view->with('rollingText', $rollingText);
        });

        // Clear cache otomatis saat RollingText di-update dari AdminController
        // Panggil Cache::forget('mpnweb_rolling_text') setelah update di controller
    }
}
