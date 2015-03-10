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
        Client::defaultToken('CAAXDftrRQX8BADEm9EGLgDgE5x9UeZBYsBWdwmosEKh8IKg4I5ZAZCMEaXFCAJL46rf1O4cerKJ1DQ4BZBWEjf1lq0FGZCZAgjS9TZBZCyo0MMrddtxAhXQ068rdksY5bAXXsgKw1wqOgTCeJUPDttV1Oaq9bj9pJLBGPI8k9tnnZAWTMEY2hnLOvlNOtc6SJU0ZAMA9xZBKGk50yCZAI8cxxBveMCT2Plah9EIZD');
    }
}
