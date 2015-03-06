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
        Client::defaultToken('CAAXDftrRQX8BAHZBNnOaoFoDeQ5XOAYvfZCab6dYiAKvZAkZCkmwppf26vsaAlUZC3ZBsb5VFDVZAMFKV2vZBaiezGZBc8SjCNKgmF2t1VkhgUgZCkdyuhcCMJcsA5BV8lqlBfw0ZAG5fPouQP7hRmPv2N82A21ZB7C1UNCSW1VEGlFWt6DXtDkxnNbZAEK0ooecOecmEZCt7QhWAIvZA62IeWeHSC1');
    }
}
