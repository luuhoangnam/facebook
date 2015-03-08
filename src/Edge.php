<?php


namespace Namest\Facebook;


/**
 * Class Edge
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Edge
{
    const IN = 'in';
    const OUT = 'out';

    const SINGLE = 'single';
    const COLLECTION = 'collection';

    /**
     * @var string
     */
    protected $direction;

    /**
     * @var Object
     */
    protected $start;

    /**
     * @var string
     */
    protected $relation;

    /**
     * @var Object
     */
    protected $end;

    /**
     * @var string
     */
    protected $edge;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Object $start
     * @param string $relation
     * @param Object $end
     * @param string $edge
     * @param array  $options
     *
     * @throws Exception
     *
     */
    public function __construct($start, $relation, $end, $edge, $options = [])
    {
        if ( ! in_array($this->direction, [Edge::IN, Edge::OUT]))
            throw new Exception("Edge direction must be set via class inheritance");

        if ( ! in_array('cast', $options))
            $options['cast'] = Edge::SINGLE;

        if ( ! in_array($options['cast'], [Edge::SINGLE, Edge::COLLECTION]))
            throw new \InvalidArgumentException("Result casting must be value of Edge::SINGLE or Edge::COLLECTION.");

        $this->start    = $start;
        $this->relation = $relation;
        $this->end      = $end;
        $this->edge     = $edge;
        $this->options  = $options;
    }

    /**
     * @return \StdClass
     */
    public function fetch()
    {
        if ($this->edge === false)
            throw new \BadMethodCallException("Can not call fetch() method on non facebook edge.");

        $parameters = [];
        if ($this->hasFetchParameters())
            $parameters = $this->getFetchParameters();

        if ($this->hasFetchFields())
            $parameters['fields'] = $this->getFetchFields();

        $results = $this->realFetch($parameters);

        return $results;
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return $this->client = $this->client ?: new Client;
    }

    /**
     * @return bool
     */
    private function hasFetchParameters()
    {
        return method_exists($this->start, 'getFetch' . studly_case($this->edge) . 'Parameters');
    }

    /**
     * @return array
     */
    protected function getFetchParameters()
    {
        return call_user_func([$this->start, 'getFetch' . studly_case($this->edge) . 'Parameters']);
    }

    /**
     * @return bool
     */
    private function hasFetchFields()
    {
        return method_exists($this->start, 'getFetch' . studly_case($this->edge) . 'Fields');
    }

    /**
     * @return string
     */
    protected function getFetchFields()
    {
        return call_user_func([$this->start, 'getFetch' . studly_case($this->edge) . 'Fields']);
    }

    /**
     * @param array $parameters
     *
     * @return \StdClass|array
     */
    protected function realFetch(array $parameters)
    {
        $collection = [];

        $client   = $this->getClient();
        $response = $client->get("{$this->start->id}/{$this->edge}", $parameters);

        while (property_exists($response, 'data')) {
            foreach ($response->data as $item) {
                $collection[] = $item;
            }

            if (($request = $client->getResponse()->getRequestForNextPage()))
                $response = $this->client->send($request);
            else
                $response = new \StdClass;
        }

        return $collection ?: $response;
    }

    /**
     * @param array $fields
     *
     * @return mixed
     */
    public function publish(array $fields)
    {
        $client = $this->getClient();

        $response = $client->post("{$this->start->id}/{$this->edge}", $fields);

        if (property_exists($response, 'success'))
            return $response->success;

        if (property_exists($response, 'id')) {
            $id = $response->id;
            $this->end->setId($id)->sync();

            return $this->end;
        }

        return $response;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getDirection()
    {
        if ( ! in_array($this->direction, [Edge::IN, Edge::OUT]))
            throw new Exception("Edge direction must be set via class inheritance");

        return $this->direction;
    }

    /**
     * @return array|Object
     */
    public function get()
    {
        $startNodeLabel = $this->start->getLabel();
        $endNodeLabel   = $this->end->getLabel();
        $relation       = $this->relation;
        $startNodeId    = $this->start->id;
        $direction      = $this->getDirection();

        $queryString = "MATCH (start:{$startNodeLabel})"
                       . ($direction === Edge::OUT ? '<' : '')
                       . "-[relation:{$relation}]-"
                       . ($direction === Edge::IN ? '>' : '')
                       . "(end:{$endNodeLabel})
                        WHERE start.id = \"{$startNodeId}\"
                        RETURN relation, end";

        $results = $this->getCypherQuery($queryString)->getResultSet();

        if (count($results) === 0)
            return null;

        if ($this->options['cast'] === Edge::SINGLE) {
            /** @var Relationship $relationship */
            $relationship = $results->current()['relation'];

            if ($this->getDirection() === Edge::IN)
                $node = $relationship->getEndNode();
            else
                $node = $relationship->getStartNode();

            $class = get_class($this->end);

            $properties = $node->getProperties();

            $properties['relationship'] = $relationship->getProperties();

            return new $class($properties);
        }

        // Cast to collection
        $collection = new Collection;
        foreach ($results as $result) {
            /** @var Node $node */
            $node       = $result['end'];
            $properties = $node->getProperties();
            /** @var Relationship $relationship */
            $relationship = $result['relation'];

            $properties['relationship'] = $relationship->getProperties();

            $endClass     = get_class($this->end);
            $collection[] = new $endClass($properties);
        }

        return $collection;
    }

    /**
     * @param string $statement
     *
     * @return Query
     * @throws Exception
     */
    protected function getCypherQuery($statement)
    {
        $client = $this->start->getNode()->getClient() ?: $this->end->getNode()->getClient() ?: null;
        if (is_null($client))
            throw new Exception("Can not find appropriate Neo4j client from start or end node of this edge.");

        return new Query($client, $statement);
    }
    /**
     * Save the end node
     *
     * @param $object
     *
     * @throws Exception
     * @throws \Everyman\Neo4j\Exception
     *
     * @return \StdClass
     */
    public function save(Object $object)
    {
        $this->end->fill($object->toArray());

        $relationship = $this->createUniqueRelationship();

        if ($this->getDirection() === Edge::IN)
            $properties = $relationship->getEndNode()->getProperties();
        else
            $properties = $relationship->getStartNode()->getProperties();

        $class = get_class($this->end);

        return new $class($properties);
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    protected function findRelationships()
    {
        $this->start->save();

        /** @var Node $node */
        $node = $this->start->getNode();

        return $node->getRelationships($this->relation, $this->getDirection());
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function createUniqueRelationship()
    {
        $startNodeLabel = $this->start->getLabel();
        $endNodeLabel   = $this->end->getLabel();
        $relation       = $this->relation;
        $startNodeId    = $this->start->id;
        $endNodeId      = $this->end->id;
        $direction      = $this->getDirection();

        $queryString = "MATCH (start:{$startNodeLabel}),(end:{$endNodeLabel})
                        WHERE start.id = \"{$startNodeId}\"
                            AND end.id = \"{$endNodeId}\"
                        CREATE UNIQUE (start)"
                       . ($direction === Edge::OUT ? '<' : '')
                       . "-[relation:{$relation}]-"
                       . ($direction === Edge::IN ? '>' : '')
                       . "(end)
                        RETURN relation";

        $results = $this->getCypherQuery($queryString)->getResultSet();

        $relationship = $results->current()['relation'];

        return $relationship;
    }
}