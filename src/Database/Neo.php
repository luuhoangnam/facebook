<?php

namespace Namest\Facebook\Database;

use Everyman\Neo4j\Client;

/**
 * Class Neo
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook\Database
 *
 */
class Neo
{
    protected static $client;

    /**
     * @param Client $client
     */
    public static function setClient($client)
    {
        static::$client = $client;
    }

    /**
     * @return Client
     */
    public static function getClient()
    {
        if (is_null(static::$client))
            return new Client;

        return static::$client;
    }
}
