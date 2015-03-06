<?php

namespace Namest\Facebook;

use Exception;
use Facebook\FacebookAuthorizationException;
use Facebook\FacebookClientException;
use Facebook\FacebookOtherException;
use Facebook\FacebookPermissionException;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookResponse;
use Facebook\FacebookServerException;
use Facebook\FacebookSession;
use Facebook\FacebookThrottleException;

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
    private static $token;

    /**
     * @var FacebookSession
     */
    private $session;

    /**
     * @var FacebookResponse
     */
    private $response;

    /**
     * @var FacebookRequest
     */
    private $request;

    /**
     * @param string $token
     */
    public function __construct($token = null)
    {
        if ( ! is_null($token))
            static::$token = $token;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return \StdClass
     * @throws FacebookRequestException
     * @throws Exception
     */
    protected function request($method, $endpoint, $parameters = [])
    {
        if (is_null(static::$token))
            throw new \Exception("Token is not set!");

        try {
            $this->session  = $this->getSession();
            $endpoint       = $this->normalizeEndpoint($endpoint);
            $this->request  = new FacebookRequest($this->session, $method, $endpoint, $parameters);
            $this->response = $this->request->execute();

            return $this->response->getResponse();
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
     * @return string
     */
    public static function getToken()
    {
        return self::$token;
    }

    /**
     * @return FacebookResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return FacebookRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return \StdClass
     */
    public function get($endpoint, $parameters = [])
    {
        return $this->request('GET', $endpoint, $parameters);
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return \StdClass
     */
    public function post($endpoint, $parameters = [])
    {
        return $this->request('POST', $endpoint, $parameters);
    }

    /**
     * @param string $endpoint
     * @param array  $parameters
     *
     * @return \StdClass
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

    /**
     * @param string $token
     */
    public static function defaultToken($token)
    {
        static::$token = $token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        static::$token = $token;
    }

    /**
     * @return FacebookSession
     */
    public function getSession()
    {
        return $this->session ?: new FacebookSession(static::$token);
    }
}
