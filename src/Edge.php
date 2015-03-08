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
}