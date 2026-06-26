<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use App\Events\AduanAssigned;
use App\Events\AduanCreated;
use App\Events\AduanUpdated;
use App\Listeners\SendAduanPushNotification;
use App\Models\Aduan;
use App\Observers\AduanObserver;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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
        Event::listen(AduanCreated::class, SendAduanPushNotification::class);
        Event::listen(AduanAssigned::class, SendAduanPushNotification::class);
        Event::listen(AduanUpdated::class, SendAduanPushNotification::class);

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });
    }
}
