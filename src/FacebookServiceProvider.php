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
        Client::defaultToken('CAAXDftrRQX8BALcDDZCSfcONZBs76rc1ZAYpJtRs0tqaSwFnGHzCuk2Uqcx9bhpS4Upde0ZBm58azPdR0SEwX8E9PebzuU4BZAgEtiaHvnvWIJbU6WhlwKiaF5P4CpsdrtNGdZCGqrijfYiSNhibJ8ZBK4GMkug4rCClVN9mgapRQQkYdNTlIhWBDiDDLbsPjjtZCPKZAioU2AnIZBiPCvfTFAZAJ6hmj90g4cZD');
    }
}
