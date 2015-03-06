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
        Client::defaultToken('CAAXDftrRQX8BAGMUp0HdEYBDwhvm9nJe0oq5B2BcjKT3QM6B1EyyKNW3MXFydHxd11eQWaHan3Mf2rqvNLhbZB4pwP16NgFY5eLQg8RnwKCgG3hFrsZCZAbCpaEyYFvUxZAmZBVm8vGd96cZAyH9fWcrjbzph7X4LEjXNcIVXxWlxKAiUMkOZB1U43xpiCfHZCL9SyHaAZA3z8LPJS5JXIFmZA');
    }
}
