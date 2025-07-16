<?php

namespace App\Providers;

use App\Enums\MessageTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        RedirectResponse::macro('withMessage', function (string $message, MessageTypeEnum $messageType = MessageTypeEnum::default) {
            Session::flash('messageType', $messageType->value);
//            Session::flash('messageTitle', $messageTitle);
            Session::flash('message', $message);

            return $this;
        });

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();
    }
}
