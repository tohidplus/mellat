<?php

namespace Tohidplus\Mellat;

use Illuminate\Support\ServiceProvider;
use SoapClient;

class MellatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/mellat.php', 'mellat'
        );
        $this->publishes([
            __DIR__.'/config/mellat.php' => config_path('mellat.php'),
            __DIR__.'/views' => resource_path('views/vendor/mellat'),
        ]);
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/views','mellat');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mellat',function (){
            $client = new SoapClient('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
            return new MellatBank($client);
        });
    }
}
