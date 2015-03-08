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
        Client::defaultToken('CAAXDftrRQX8BALz3qlZCgJXNyIYnaFckXPVRH62pzzwxx70TNo4lLNNpd18ZCJ6GiSk9m3yE0YtevDEacFvF6gRyoBvhP1afStCmk1kL3WYZBnElQ9VGJ7kIlPqoftyF5OUEuaZCXHA1KYCvhpHuuLDkHZB5xBOYdxn4i1XMPbmVCds3ZCAeJuDsKhmwVns2InwXZA5cpehkvtvVZCS3YbOCDDULhBznNcYZD');
    }
}
