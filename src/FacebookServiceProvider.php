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
        Client::defaultToken('CAAXDftrRQX8BAFZBybHMp5NJs5H3y6gtjbMEsZCol7z0zPfWpzZCg4l5jOUrnnELuPQuSNz2b3m4DLXmqgf4iHH2ddRUIfQZBuZAJgOuvMv6ZA6ZCqmodqucnnQ9tQI0mRq6cdV5WHHQhsMJHJvgKcfD6QgiWwimewTQlrc15vq7XU4dviREUeNhJcjgDCmZAgZCNCJAWBEyZBGEJlIRdZAvZCB1AA5448ZBSXGUZD');
    }
}
