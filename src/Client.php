<?php

namespace Namest\Facebook;

use Exception;
use Facebook\FacebookAuthorizationException;
use Facebook\FacebookClientException;
use Facebook\FacebookOtherException;
use Facebook\FacebookPermissionException;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookServerException;
use Facebook\FacebookSession;
use Facebook\FacebookThrottleException;
use Facebook\GraphObject;

/**
 * Class Client
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Client
{

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return GraphObject
     * @throws FacebookRequestException
     * @throws Exception
     */
    protected function request($method, $endpoint, $parameters = [])
    {
        try {
            $session  = new FacebookSession($this->token);
            $endpoint = $this->normalizeEndpoint($endpoint);
            $request  = new FacebookRequest($session, $method, $endpoint, $parameters);
            $response = $request->execute();

            return $response->getGraphObject();
        } catch ( FacebookClientException $e ) {
            //
            throw $e;
        } catch ( FacebookServerException $e ) {
            //
            throw $e;
        } catch ( FacebookAuthorizationException $e ) {
            //
            throw $e;
        } catch ( FacebookPermissionException $e ) {
            //
            throw $e;
        } catch ( FacebookThrottleException $e ) {
            //
            throw $e;
        } catch ( FacebookOtherException $e ) {
            //
            throw $e;
        } catch ( FacebookRequestException $e ) {
            //
            throw $e;
        } catch ( Exception $e ) {
            //
            throw $e;
        }
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return GraphObject
     */
    public function get($endpoint, $parameters = [])
    {
        return $this->request('GET', $endpoint, $parameters);
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return GraphObject
     */
    public function post($endpoint, $parameters = [])
    {
        return $this->request('POST', $endpoint, $parameters);
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return GraphObject
     */
    public function delete($endpoint, $parameters = [])
    {
        return $this->request('DELETE', $endpoint, $parameters);
    }

    /**
     * @param string $endpoint
     *
     * @return string
     */
    private function normalizeEndpoint($endpoint)
    {
        if (substr($endpoint, 0, 1) != '/')
            $endpoint = "/{$endpoint}";

        return $endpoint;
    }
}
