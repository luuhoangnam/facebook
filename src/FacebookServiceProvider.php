<?php

namespace Namest\Facebook;

use Facebook\FacebookSession;
use Illuminate\Support\ServiceProvider;

/**
 * Class FacebookServiceProvider
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class FacebookServiceProvider extends ServiceProvider
{
    /**
     * Boot up resources
     */
    public function boot()
    {
        // Publish a config file
        $this->publishes([
            __DIR__ . '/../config/facebook.php' => config_path('facebook.php')
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFacebookServices();

        Object::setEventDispatcher($this->app['events']);
    }

    protected function registerFacebookServices()
    {
        $appId     = $this->app['config']->get('facebook.app_id');
        $appSecret = $this->app['config']->get('facebook.app_secret');

        FacebookSession::setDefaultApplication($appId, $appSecret);
        Client::defaultToken('CAAXDftrRQX8BAHSyn8Lx1GT1RO9GAtE4pUY72bVEPb05QKpWBHClLncCnuGBk6dZB2Leqy3sEZClqUo2oWExMDime36rszSFsB7iUnTYQIcYdcZBxAeQ7cz7HICg0vzAOehEQDoZBJaPpnwNemngLSKnbiNTWo6USIqTLeYtHtBZAVSTvEV7NLd3gTL6RGYfb92omsnuwchn7wpgTNEGZA');
    }
}
