<?php

namespace Namest\Facebook;

/**
 * Class Object
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 * @property string id
 *
 */
class Object implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $editable = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $required = ['id'];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->required) && ! array_key_exists($key, $this->attributes))
            throw new \LogicException("[{$key}] is required to be set.");

        return $this->attributes[$key];
    }

    public function get()
    {
        // TODO Implements get
    }

    public function sync()
    {
        // Fetch
        $attributes = (array) $this->fetch();

        // Set
        $this->fill($attributes);

        // Save
        // TODO Implements sync
    }

    /**
     * @return \StdClass
     */
    public function fetch()
    {
        if ( ! is_string($this->id) || $this->id == '')
            throw new \LogicException("Facebook ID need to be set before fetch information.");

        return $this->getClient()->get($this->id);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client = $this->client ?: new Client;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->$key);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->$offset;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->$offset = $value;
    }

    /**
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->$offset);
    }

    /**
     * @param array $fields
     *
     * @return bool
     */
    public function update(array $fields)
    {
        $client = $this->getClient();

        $response = $client->post("{$this->id}", $fields);

        return $response->success;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $client = $this->getClient();

        $response = $client->delete("{$this->id}");

        // TODO Sync with database

        return $response->success;
    }

    /**
     * @param string $object
     * @param string $relation
     * @param string $edge
     * @param string $direction
     *
     * @return EdgeOut
     */
    protected function hasMany($object, $relation, $edge = null, $direction = Edge::OUT)
    {
        if (is_null($edge)) {
            list(, $caller) = debug_backtrace(false, 2);

            $edge = $caller['function'];
        }

        switch ($direction) {
            case Edge::OUT:
                return new EdgeOut($this, $relation, new $object, $edge);
            case Edge::IN:
                return new EdgeIn($this, $relation, new $object, $edge);
            default:
                throw new \InvalidArgumentException("Not support direction [{$direction}]");
        }
    }
}
