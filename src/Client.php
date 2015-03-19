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

        $this->session = $this->getSession();
        $endpoint      = $this->normalizeEndpoint($endpoint);
        $parameters    = $this->normalizeParameters($parameters);

        $this->request = new FacebookRequest($this->session, $method, $endpoint, $parameters);

        return $this->send($this->request);
    }

    /**
     * @param FacebookRequest $request
     *
     * @return array
     * @throws Exception
     * @throws FacebookAuthorizationException
     * @throws FacebookClientException
     * @throws FacebookOtherException
     * @throws FacebookPermissionException
     * @throws FacebookRequestException
     * @throws FacebookServerException
     * @throws FacebookThrottleException
     */
    public function send(FacebookRequest $request)
    {
        try {
            $this->response = $request->execute();

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
        static::setToken($token);
    }

    /**
     * @param string $token
     */
    public static function setToken($token)
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
     * @return array
     */
    protected function normalizeParameters(array $parameters)
    {
        if (array_key_exists('fields', $parameters))
            $parameters['fields'] = $this->buildFieldsFromArray($parameters['fields']);

        return $parameters;
    }

    /**
     * @param array|\StdClass|Object $data
     *
     * @return string
     */
    public function guestProfileTypeFromData($data)
    {
        if ($data instanceof \StdClass)
            $data = (array) $data;

        if ($data instanceof Object)
            $data = $data->toArray();

        if (array_key_exists('category', $data))
            return Profile::PAGE;

        return Profile::USER;
    }

    /**
     * @param array|\StdClass|Object $data
     * @param array                  $properties
     *
     * @return Application|Event|Group|Page|User
     */
    public function newProfileFromData($data, $properties = [])
    {
        $profileType = $this->guestProfileTypeFromData($data);

        switch ($profileType) {
            case Profile::APPLICATION:
                $profile = new Application($properties);
                break;
            case Profile::GROUP:
                $profile = new Group($properties);
                break;
            case Profile::EVENT:
                $profile = new Event($properties);
                break;
            case Profile::PAGE:
                $profile = new Page($properties);
                break;
            case Profile::USER:
            default:
                $profile = new User($properties);
        }

        return $profile;
    }

    /**
     * @param string $shortLivedToken
     *
     * @return string
     */
    public static function getLongLivedToken($shortLivedToken = null)
    {
        $instance = new static;

        $shortLivedToken = $shortLivedToken ?: $instance->getSession()->getToken();

        if ( ! $shortLivedToken)
            throw new \LogicException("Can not get long lived token until you specify short lived token.");

        $parameters = [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => env('FACEBOOK_APP_ID'),
            'client_secret'     => env('FACEBOOK_APP_SECRET'),
            'fb_exchange_token' => $shortLivedToken,
        ];

        $response = $instance->get('oauth/access_token', $parameters);

        return $response['access_token'];
    }
}
