<?php

namespace Namest\Facebook;

use Facebook\FacebookSession;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Everyman\Neo4j\Client as Neo;

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
        $this->registerNeoClient();

        Object::setEventDispatcher($this->getEventDispatcher());
    }

    /**
     *
     */
    protected function registerFacebookServices()
    {
        $appId     = $this->app['config']->get('facebook.app_id');
        $appSecret = $this->app['config']->get('facebook.app_secret');

        FacebookSession::setDefaultApplication($appId, $appSecret);
    }

    /**
     * @return Dispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->app['events'];
    }

    /**
     *
     */
    protected function registerNeoClient()
    {
        // Example:
        // https://54fdcdd7a1025:BwWPxfDikLh275uL9Oht8mMJoRL7ckREws0FEyd6@neo-54fdcd4fa1064-364459c455.do-stories.graphstory.com:7474

        $transport = env('NEO4J_HOST');
        $port      = env('NEO4J_PORT');
        $username  = env('NEO4J_USERNAME');
        $password  = env('NEO4J_PASSWORD');

        $client = new Neo($transport, $port);
        $client->getTransport()
               ->useHttps()
               ->setAuth($username, $password);

    }
}
