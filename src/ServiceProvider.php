<?php

namespace EmailChangeVerification;

use EmailChangeVerification\Broker\BrokerManager;
use EmailChangeVerification\Commands\ClearEmailChangesCommand;
use Illuminate\Contracts\Support\DeferrableProvider;

class ServiceProvider extends \Illuminate\Support\ServiceProvider implements DeferrableProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/email-change-verification.php' => config_path('email-change-verification.php'),
            ], 'config');


            $this->commands([
                ClearEmailChangesCommand::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/email-change-verification.php', 'email-change-verification');

        $this->registerEmailChangeBroker();
    }

    /**
     * Register the email change broker instance.
     *
     * @return void
     */
    protected function registerEmailChangeBroker()
    {
        $this->app->singleton('auth.email_changes', function ($app) {
            return new BrokerManager($app);
        });

        $this->app->bind('auth.email_changes.broker', function ($app) {
            return $app->make('auth.email_changes')->broker();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ 'auth.email_changes', 'auth.email_changes.broker' ];
    }
}
