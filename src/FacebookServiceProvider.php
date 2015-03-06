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
        Client::defaultToken('CAAXDftrRQX8BAKLVUHsTUtYVZCi6KSzcbC8Llvm3r72Y9jqzpZCOcQJjXZByE29ZA4A6e9RGdg0odP2xkI1r3SzKLP0zZA04wlHFT4EjZAHG3kK3k1YfkAHBzXdtPZC081oUXHPlg8abtsAI4EP3pkO5EflKTTTdm2Gje2hJYmDPyVNhYZCT2BnefZCeo3iTqhJlUiw8lgSXZAeuBRWZChlLCMa');
    }
}
