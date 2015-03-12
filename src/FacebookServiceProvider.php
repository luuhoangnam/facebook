<?php

namespace Namest\Facebook;

use Facebook\FacebookSession;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Everyman\Neo4j\Client as NeoClient;
use Namest\Facebook\Console\Comment as CommentCommand;
use Namest\Facebook\Console\Page as PageCommand;
use Namest\Facebook\Console\Post as PostCommand;
use Namest\Facebook\Console\User as UserCommand;
use Namest\Facebook\Database\Neo;

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

        $this->app->singleton('namest.facebook.console.user', function () {
            return $this->app->make(UserCommand::class);
        });

        $this->app->singleton('namest.facebook.console.page', function () {
            return $this->app->make(PageCommand::class);
        });

        $this->app->singleton('namest.facebook.console.post', function () {
            return $this->app->make(PostCommand::class);
        });

        $this->app->singleton('namest.facebook.console.comment', function () {
            return $this->app->make(CommentCommand::class);
        });

        $this->commands([
            'namest.facebook.console.user',
            'namest.facebook.console.page',
            'namest.facebook.console.post',
            'namest.facebook.console.comment',
        ]);
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

        $configuration = $this->app['config']->get('facebook.connections.neo4j');

        $host     = $configuration['host'];
        $port     = $configuration['port'];
        $https    = $configuration['https'];
        $username = $configuration['username'];
        $password = $configuration['password'];

        $client    = new NeoClient($host, $port);
        $transport = $client->getTransport();

        if ($https)
            $transport->useHttps()->setAuth($username, $password);

        $client->setTransport($transport);

        Neo::setClient($client);
    }
}
