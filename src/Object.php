<?php

namespace Namest\Facebook;

use ArrayAccess;
use Everyman\Neo4j\Client as Neo4jClient;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Node;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Object
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 * @property string id
 *
 */
class Object implements ArrayAccess, Arrayable
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
     * @var Node
     */
    protected $node;

    /**
     * @var string
     */
    protected $label;

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

    /**
     * @return $this
     */
    public function get()
    {
        $this->findNode();
        $properties = $this->node->getProperties();

        $this->fill($properties);

        return $this;
    }

    /**
     * @return $this
     */
    public function sync()
    {
        // Fetch
        $attributes = (array) $this->fetch();

        // Set
        $this->fill($attributes);

        // Save
        $this->save();

        return $this;
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
                /** @noinspection PhpParamsInspection */
                return new EdgeOut($this, $relation, new $object, $edge);
            case Edge::IN:
                /** @noinspection PhpParamsInspection */
                return new EdgeIn($this, $relation, new $object, $edge);
            default:
                throw new \InvalidArgumentException("Not support direction [{$direction}]");
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * @return Node
     */
    public function save()
    {
        $this->node = $this->findNode();
        if (is_null($this->node)) {
            // CREATE
            return $this->node = $this->createNode();
        }

        // UPDATE
        return $this->node = $this->saveNode();
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node ?: new Node(new Neo4jClient);
    }

    /**
     * @return string
     */
    protected function getLabel()
    {
        $className = get_class($this);

        $segments = explode('\\', $className);

        $label = array_pop($segments);

        return $this->label ?: $label;
    }

    /**
     * @param string $statement
     *
     * @return Query
     */
    protected function getCypherQuery($statement)
    {
        return new Query($this->getNode()->getClient(), $statement);
    }

    /**
     * @return Node
     */
    public function findNode()
    {
        $label       = $this->getLabel();
        $queryString = "MATCH (node:{$label})
                        WHERE node.id = \"{$this->id}\"
                        RETURN node";

        $results = $this->getCypherQuery($queryString)->getResultSet();

        if (count($results) === 0)
            return null;

        return $results[0]['node'];
    }

    /**
     * @param Node $node
     *
     * @return Node
     */
    protected function createNode($node = null)
    {
        $node = $node ?: $this->getNode();

        $this->saveNode($node);

        $this->addLabel($this->getLabel());

        return $node;
    }

    /**
     * @param Node $node
     *
     * @return Node
     */
    protected function saveNode($node = null)
    {
        $node = $node ?: $this->getNode();

        $node->setProperties($this->attributes);
        $node->save();

        return $node;
    }

    /**
     * @param string $label
     */
    protected function addLabel($label = null)
    {
        $label = $label ?: $this->getLabel();

        $label = $this->getNode()->getClient()->makeLabel($label);
        $this->node->addLabels([$label]);
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
