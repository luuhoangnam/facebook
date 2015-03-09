<?php

namespace Namest\Facebook;

use ArrayAccess;
use Closure;
use Everyman\Neo4j\Client as Neo4jClient;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Node;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use LogicException;

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
     * @var array
     */
    protected $fields = [];

    /**
     * The event dispatcher instance.
     *
     * @var Dispatcher
     */
    protected static $dispatcher;

    /**
     * @var array
     */
    protected $edges = [];

    /**
     * @var mixed
     */
    protected $relationship;

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

        if (array_key_exists($key, $this->edges)) {
            return $this->edges[$key];
        }

        if (method_exists($this, $key)) {
            return $this->getEdgeFromMethod($key);
        }

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

        $parameters = $this->fields ? ['fields' => $this->fields] : [];

        return $this->getClient()->get($this->id, $parameters);
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
     * @param mixed $relationship
     */
    public function setRelationship($relationship)
    {
        $this->relationship = $relationship;
    }

    /**
     * @param string $object
     * @param string $relation
     * @param string $edge
     * @param string $direction
     * @param array  $options
     *
     * @return EdgeOut|EdgeIn
     *
     */
    protected function hasMany($object, $relation, $edge = null, $direction = Edge::OUT, $options = [])
    {
        if (is_null($edge)) {
            list(, $caller) = debug_backtrace(false, 2);

            $edge = $caller['function'];
        }

        if ( ! isset($options['cast']))
            $options['cast'] = Edge::COLLECTION;

        return $this->makeEdge($object, $relation, $edge, $direction, $options);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (is_array($this->relationship))
            return array_merge($this->attributes, $this->relationship);

        return $this->attributes;
    }

    /**
     * @return Node
     */
    public function save()
    {
        if ($this->fireEvent('saving') === false)
            return false;

        $result = $this->performSave();

        if ($result)
            $this->fireEvent('saved', false);

        return $result;
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
    public function getLabel()
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

        $this->node = $this->saveNode($node);

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

        $this->setNodeProperties($node);
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

    /**
     * @param Node  $node
     * @param array $attributes
     */
    protected function setNodeProperties($node = null, $attributes = [])
    {
        $node       = $node ?: $this->getNode();
        $attributes = $attributes ?: $this->attributes;

        foreach ($attributes as $key => $value) {
            if ($this->hasHydrator($key)) {
                $value = $this->hydrateField($key, $value);
            }

            $node->setProperty($key, $value);
        }
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    private function hasHydrator($field)
    {
        return method_exists($this, 'hydrate' . studly_case($field) . 'Field');
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return mixed
     */
    public function hydrateField($field, $value)
    {
        return $this->{'hydrate' . studly_case($field) . 'Field'}($value);
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return void
     */
    public static function setEventDispatcher(Dispatcher $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * @return void
     */
    public static function unsetEventDispatcher()
    {
        static::$dispatcher = null;
    }

    /**
     * @param string $event
     * @param bool   $halt
     *
     * @return mixed
     */
    protected function fireEvent($event, $halt = true)
    {
        if ( ! isset(static::$dispatcher))
            return true;

        // We will append the names of the class to the event to distinguish it from
        // other model events that are fired, allowing us to listen on each model
        // event set individually instead of catching event for all the models.
        $event = "namest.facebook.{$event}: " . get_class($this);

        $method = $halt ? 'until' : 'fire';

        return static::$dispatcher->$method($event, $this);
    }

    /**
     * @param string         $event
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    protected static function registerEvent($event, $callback, $priority = 0)
    {
        if (isset(static::$dispatcher)) {
            $name = get_called_class();

            static::$dispatcher->listen("namest.facebook.{$event}: {$name}", $callback, $priority);
        }
    }

    /**
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    public static function saving($callback, $priority = 0)
    {
        static::registerEvent('saving', $callback, $priority);
    }

    /**
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    public static function saved($callback, $priority = 0)
    {
        static::registerEvent('saved', $callback, $priority);
    }

    /**
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    public static function creating($callback, $priority = 0)
    {
        static::registerEvent('creating', $callback, $priority);
    }

    /**
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    public static function created($callback, $priority = 0)
    {
        static::registerEvent('created', $callback, $priority);
    }

    /**
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    public static function deleting($callback, $priority = 0)
    {
        static::registerEvent('deleting', $callback, $priority);
    }

    /**
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    public static function deleted($callback, $priority = 0)
    {
        static::registerEvent('deleted', $callback, $priority);
    }

    /**
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    public static function updating($callback, $priority = 0)
    {
        static::registerEvent('updating', $callback, $priority);
    }

    /**
     * @param Closure|string $callback
     * @param int            $priority
     *
     * @return void
     */
    public static function updated($callback, $priority = 0)
    {
        static::registerEvent('updated', $callback, $priority);
    }

    /**
     * @return bool|Node
     */
    protected function performCreate()
    {
        if ($this->fireEvent('creating') === false)
            return false;

        $this->node = $this->createNode();

        $this->fireEvent('created', false);

        return $this->node;
    }

    /**
     * @return bool|Node
     */
    protected function performUpdate()
    {
        if ($this->fireEvent('updating') === false)
            return false;

        $this->node = $this->saveNode();

        $this->fireEvent('updated', false);

        return $this->node;
    }

    /**
     * @return bool|Node
     */
    protected function performSave()
    {
        $this->node = $this->findNode();

        if (is_null($this->node))
            // CREATE
            return $this->performCreate();

        // UPDATE
        return $this->performUpdate();
    }

    /**
     * @param string $object
     * @param string $relation
     * @param string $edge
     * @param string $direction
     * @param array  $options
     *
     * @return EdgeOut
     *
     */
    protected function belongsTo($object, $relation, $edge = null, $direction = Edge::OUT, $options = [])
    {
        // (profile:Profile)-[r1:LEAVE]->(c:Comment)-[r2:ON]->(post:Post)
        if (is_null($edge)) {
            list(, $caller) = debug_backtrace(false, 2);

            $edge = $caller['function'];
        }

        if ( ! isset($options['cast']))
            $options['cast'] = Edge::SINGLE;

        return $this->makeEdge($object, $relation, $edge, $direction, $options);
    }

    /**
     * @param string $object
     * @param string $relation
     * @param string $edge
     * @param string $direction
     * @param array  $options
     *
     * @return EdgeIn|EdgeOut
     *
     */
    protected function makeEdge($object, $relation, $edge, $direction, $options = [])
    {
        switch ($direction) {
            case Edge::OUT:
                /** @noinspection PhpParamsInspection */
                return new EdgeOut($this, $relation, new $object, $edge, $options);
            case Edge::IN:
                /** @noinspection PhpParamsInspection */
                return new EdgeIn($this, $relation, new $object, $edge, $options);
            default:
                throw new \InvalidArgumentException("Not support direction [{$direction}]");
        }
    }

    /**
     * @param string $method
     *
     * @return array|Object
     */
    private function getEdgeFromMethod($method)
    {
        $edge = $this->$method();

        if ( ! $edge instanceof Edge) {
            throw new LogicException('Edge method must return an object of type ' . Edge::class);
        }

        return $this->edges[$method] = $edge->get();
    }

    /**
     * @param $event
     */
    public function unsetEvent($event)
    {
        if (isset(static::$dispatcher)) {
            $name = get_called_class();
            
            static::$dispatcher->forget("namest.facebook.{$event}: {$name}");
        }
    }
}
