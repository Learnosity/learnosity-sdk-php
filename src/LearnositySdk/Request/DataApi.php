<?php

namespace LearnositySdk\Request;

use \Exception;
use \LearnositySdk\Request\Init;
use \LearnositySdk\Request\Remote;
use \LearnositySdk\Utils\Json;
use \LearnositySdk\Utils\Log;

/**
 *--------------------------------------------------------------------------
 * Learnosity SDK - DataApi
 *--------------------------------------------------------------------------
 *
 * Used to make requests to the Learnosity Data API - including
 * generating the security packet
 *
 */

class DataApi
{
    private $log;
    private $remote;
    private $remoteOptions;

    /**
     * @param array $remoteOptions Overrides options array for a cURL request
     */
    public function __construct($remoteOptions = array(), $logOptions = null)
    {
        $this->log = new Log($logOptions);
        $this->remote = new Remote();
        $this->remoteOptions = $remoteOptions;
    }

    /**
     * Makes a single request to the data api
     *
     * @param  string  $endpoint       URL to send the request
     * @param  array   $securityPacket Security details
     * @param  string  $secret         Private key
     * @param  array   $requestPacket  Request packet
     * @param  string  $action         Action for the request
     * @return Remote                  Instance of the Remote class,
     *                                 the response can be obtained with the getBody() method
     */
    public function request($endpoint, $securityPacket, $secret, $requestPacket = null, $action = null)
    {
        $this->log->write(
            'request',
            array(
                parse_url($endpoint, PHP_URL_PATH),
                $action,
                json_encode($requestPacket)
            )
        );

        $init = new Init('data', $securityPacket, $secret, $requestPacket, $action);
        $params = $init->generate();
        $response = $this->remote->post($endpoint, $params, $this->remoteOptions);

        $this->log->write(
            'response',
            array(
                parse_url($endpoint, PHP_URL_PATH),
                $response->getStatusCode(),
                (string)round($response->getTimeTaken(), 2),
                $response->getBody()
            )
        );
        $this->log->write(
            'summary',
            array(
                parse_url($endpoint, PHP_URL_PATH),
                $response->getStatusCode(),
                (string)round($response->getTimeTaken(), 2)
            )
        );

        return $response;
    }

    /**
     * Makes a recursive request to the data api, dependant on
     * whether 'next' is returned in the meta object
     *
     * @param  string  $endpoint       URL to send the request
     * @param  array   $securityPacket Security details
     * @param  string  $secret         Private key
     * @param  array   $requestPacket  Request packet
     * @param  string  $action         Action for the request
     * @param  mixed   $callback       Optional callback to execute instead of returning data
     * @return array                   Array of all data requests or [] or using a callback
     */
    public function requestRecursive($endpoint, $securityPacket, $secret, $requestPacket = null, $action = null, $callback = null)
    {
        $response = array();

        do {
            $request = $this->request($endpoint, $securityPacket, $secret, $requestPacket, $action);
            $data = Json::isJson($request->getBody()) ? json_decode($request->getBody(), true) : $request->getBody();
            if ($data['meta']['status'] === true) {
                if (!empty($callback) && is_callable($callback)) {
                    call_user_func($callback, $data);
                } else {
                    $response = array_merge($response, $data['data']);
                }
            } else {
                $this->log->write('error', $data);
                throw new Exception(Json::encode($data));
            }
            if (array_key_exists('next', $data['meta']) && !empty($data['data'])) {
                $requestPacket['next'] = $data['meta']['next'];
            } else {
                unset($requestPacket['next']);
            }
        } while (array_key_exists('next', $requestPacket));

        return $response;
    }
}
