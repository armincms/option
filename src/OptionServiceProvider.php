<?php

namespace Armincms\Option;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider; 

class OptionServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('armincms.option', function ($app) {
            return new OptionManager($app);
        });

        $this->app->singleton('armincms.option.store', function ($app) {
            return $app['armincms.option']->driver();
        });  

        $this->registerPublishing();
    } 

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    { 
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations')
        ], ['armincms.option', 'armincms.option.migrations']);

        $this->publishes([
            __DIR__.'/../config/option.php' => config_path('option.php')
        ], ['armincms.option', 'armincms.option.config']); 
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'armincms.option', 'armincms.option.store'
        ];
    }
}
