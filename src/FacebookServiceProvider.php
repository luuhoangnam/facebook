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
        Client::defaultToken('CAAXDftrRQX8BAMjZBZBBZBhM8hUXQ8pqWi3EkZCZAQi63EZBBNW20TdYfBvCFqD22nyyDGqrOwvZBvmZAbmpruqgrrM1ZARA0GqxGn3czGQus2m9CZA2ZADdbm43TSxbxiQ2rNUVcN46RqQZBMJo8R6ICKtu5Tffhssh0pNEf40JoFRuMcdA3ZCe7z5N0QZArKZBcJ0IbEdZByIQJTTYBQyNwLN3ZCZBvMvZBEZAk6rUwiUZD');
    }
}
