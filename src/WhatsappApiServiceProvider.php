<?php

namespace navidman\whatsappApi;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class WhatsappApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('whatsapp', function() {
            return App::make('navidman\whatsappApi\WhatsappService');
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->publishes([
            __DIR__.'/config/whatsapp.php' => config_path('whatsapp.php'),
        ]);
    }
}
