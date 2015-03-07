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
        Client::defaultToken('CAAXDftrRQX8BAIXYc4ETLddzTXVdQ6SHZCSfs1bIPrtpY6IIFfmRrZCCV4IdKC1qZBYcKo0cWC57F82DdBlCaXwHtzVlUiv8wbIoGvRZA9C4bpZBxWXuc5dZBUOd29hjO1BGoPZCT9D369Ra2EE1W1LPi46MRv0CLDgMqUqCZBteZAM73qnBbOJPGcb24GZCRQvsTLaCsQtysd7ZAvlIGWqC8gC');
    }
}
