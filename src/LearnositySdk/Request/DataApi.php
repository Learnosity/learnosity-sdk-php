<?php

namespace LearnositySdk\Request;

use LearnositySdk\Request\Init;
use LearnositySdk\Request\Remote;

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
    public function request($url, $securityPacket, $secret, $requestPacket = null, $action = null)
    {
        $init   = new Init('data', $securityPacket, $secret, $requestPacket, $action);
        $params = $init->generate();
        $remote = new Remote();
        return $remote->post($url, $params);
    }
}
