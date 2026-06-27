<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use App\Models\Aduan;
use App\Observers\AduanObserver;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Vite::prefetch(concurrency: 3);

        // Aduan push notifications (replaces web 2s polling for mobile).
        // Single observer covers web + mobile create/update; events fan out to
        // the FCM listener. See docs/ADUAN_PUSH_NOTIFICATION.md.
        Aduan::observe(AduanObserver::class);
        // NOTE: SendAduanPushNotification is already auto-discovered from its
        // handle() type-hints (Laravel 11 listener discovery), so explicit
        // Event::listen() here would register it a SECOND time and fire two push
        // jobs per aduan. Discovery is the single source of truth.

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });
    }
}
