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
        $appId     = $this->app['config']->get('facebook.app_id');
        $appSecret = $this->app['config']->get('facebook.app_secret');

        FacebookSession::setDefaultApplication($appId, $appSecret);
        Client::defaultToken('CAAXDftrRQX8BAOh8XjaZAZCn1qmOfxyZBoZArypzXfFV4yrfHGnWIwQfpHbzyOM69kbBQJm1fFUWAJyXCQhZBoboIx3MIcgHIACvAHPQsPOrmnC56vvXniwlSLXfSVSAv9SZBEpIZAHZCynpJWjdpZBXuUAZCG6pERaDPwRGv2Qn7pJ5rLeBdisOuvE8PJwM4vE5zGIfzYnb1TtwZC8oZAteIz0M');
    }
}
