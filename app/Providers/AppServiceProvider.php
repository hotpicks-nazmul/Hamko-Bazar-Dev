<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // app/Helpers is in composer.json's `autoload.files`, but a deploy that copies files without
        // running `composer dump-autoload` would leave the newly added flash() helper unresolvable
        // and every group/membership write would 500. require_once keeps that from depending on the
        // autoloader having been rebuilt (the calls are no-ops when composer already loaded them).
        foreach (glob(app_path('Helpers/*.php')) as $helper) {
            require_once $helper;
        }
    }

    public function boot(): void
    {
        // Named auth limiters. `throttle:5,1` keys on the client IP alone, so every route using that
        // literal — customer login, admin login, staff login, the 2FA verify and resend endpoints —
        // shares ONE bucket: five staff-login guesses lock the admin login form for a minute, and an
        // office behind one NAT address can lock itself out. These key per route instead.
        \Illuminate\Support\Facades\RateLimiter::for('auth-login', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip() . '|login');
        });
        \Illuminate\Support\Facades\RateLimiter::for('auth-staff-login', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip() . '|staff');
        });
        \Illuminate\Support\Facades\RateLimiter::for('auth-2fa', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(6)->by($request->ip() . '|2fa');
        });
        \Illuminate\Support\Facades\RateLimiter::for('auth-2fa-resend', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(3)->by($request->ip() . '|2faresend');
        });
        \Illuminate\Support\Facades\RateLimiter::for('deploy-helper', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(3)->by($request->ip() . '|deploy');
        });

        // The observers in app/Observers were written but never registered anywhere, so the admin
        // notification bell could only ever show what the /notifications/test endpoint invented:
        // new orders, reviews, customers, refunds, tickets and low-stock/out-of-stock changes produced
        // nothing. Register them here, in one place, so no model has to be touched to add one.
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Review::observe(\App\Observers\ReviewObserver::class);
        \App\Models\Refund::observe(\App\Observers\RefundObserver::class);
        \App\Models\Ticket::observe(\App\Observers\TicketObserver::class);
        \App\Models\TicketReply::observe(\App\Observers\TicketReplyObserver::class);
        \App\Models\SellerPayout::observe(\App\Observers\SellerPayoutObserver::class);
        \App\Models\ProductQA::observe(\App\Observers\ProductQAObserver::class);

        // Force HTTPS in production only
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // Settings → General has a timezone control, and nothing used to read it: an operator could
        // "change the timezone" and no date anywhere moved. Apply the saved value at boot. Guarded
        // end to end, because a missing table (pre-install) or a bad row must never break boot, and
        // an unrecognised string falls back to config('app.timezone') from .env.
        try {
            $timezone = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'timezone')->value('value');

            if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
                date_default_timezone_set($timezone);
                config(['app.timezone' => $timezone]);
            }
        } catch (\Throwable $e) {
            // pre-install / no settings table: keep .env's timezone
        }

        // Prevent lazy loading in non-production environments (except local for debugging)
        // Model::preventLazyLoading(!app()->isProduction());
    }
}
