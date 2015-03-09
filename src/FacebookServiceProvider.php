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
        Client::defaultToken('CAAXDftrRQX8BAJmtZCaOQZCguHePR9ZB8j5mnbs47bau4OLoWjw5Gc5av7l3ozupbOEeZBo10M38ZBTQYs86sED7qFrHMRF5vzEdnjSf0kCBM2KdHGAB7gC9t0wWJfeNsJNgs8XdKZBm7XuDMSLlTHQb2gT2gSG6Rr2nhdbcBEY6nZCFo9OlnnZB21KYc1NtlgkgWFKQdXzUjvUGa2otUTSqtwZC7E7wtVBoZD');
    }
}
