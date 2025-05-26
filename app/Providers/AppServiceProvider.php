<?php

namespace App\Providers;

use App\Listeners\OrderEventListener;
use App\Listeners\UserEventListener;
use App\Models\Admin;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Midtrans\Config;
use Xendit\Configuration;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//        $this->app->bind(Authenticatable::class, Admin::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Export::polymorphicUserRelationship();

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return 'https://bulky.id/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });

        VerifyEmail::createUrlUsing(function (User $user) {
            $id = $user->getKey();
            $hash = sha1($user->getEmailForVerification());

            return "https://bulky.id/verify-email?id={$id}&hash={$hash}";
        });

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);

        \Event::subscribe(UserEventListener::class);
        \Event::subscribe(OrderEventListener::class);

        Configuration::setXenditKey(config('xendit.secret_key'));
    }
}
