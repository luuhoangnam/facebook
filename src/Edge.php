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
     * @var string
     */
    protected $direction;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Object $start
     * @param string $relation
     * @param Object $end
     * @param string $edge
     *
     * @throws \Exception
     */
    public function __construct($start, $relation, $end, $edge)
    {
        if ( ! in_array($this->direction, [Edge::IN, Edge::OUT]))
            throw new \Exception("Edge direction must be set via class inheritance");

        $this->start    = $start;
        $this->relation = $relation;
        $this->end      = $end;
        $this->edge     = $edge;
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
        $fields = call_user_func([$this->start, 'getFetch' . studly_case($this->edge) . 'Fields']);

        return $this->buildFieldsFromArray($fields);
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    private function buildFieldsFromArray($fields)
    {
        $fieldsFlatArray = [];

        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                $fieldsFlatArray[] = "{$key}{" . $this->buildFieldsFromArray($value) . '}';
                continue;
            }

            $fieldsFlatArray[] = $value;
        }

        return implode(',', $fieldsFlatArray);
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
     * @param string $fields
     *
     * @return mixed
     */
    public function publish($fields)
    {
        $client = $this->getClient();

        $response = $client->post("{$this->start->id}/{$this->edge}", $fields);

        if (property_exists($response, 'success'))
            return $response->success;

        if (property_exists($response, 'id'))
            // TODO Hydrate class
            return $response->id;

        return $response;
    }
}