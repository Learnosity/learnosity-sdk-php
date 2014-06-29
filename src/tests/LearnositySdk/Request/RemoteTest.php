<?php

namespace tests\LearnositySdk\Request;

use LearnositySdk\Request\Remote;
use LearnositySdk\Request\Init;

class RemoteTest extends \PHPUnit_Framework_TestCase
{
    /*
    public function testPost()
    {
        list($service, $security, $secret, $request, $action) = InitTest::getWorkingDataApiParams();
        $init = new Init($service, $security, $secret, $request, $action);

        $url = 'https://data.learnosity.com/latest/sessions/responses';

        $remote = new Remote();
        $ret = $remote->post($url, $init->generate());

        $this->assertInstanceOf('LearnositySdk\Request\Remote', $ret);

        $body = self::$remoteInstance->getBody();
        $arr = json_decode($body, true);

        $this->assertNotEmpty($arr['meta']['status']);
        $this->assertNotEmpty($arr['meta']['timestamp']);
        $this->assertTrue($arr['meta']['status']);
        $this->assertArrayHasKey('records', $arr['meta']);
        $this->assertEquals($arr['meta']['records'], count($arr['data']));

        return $ret;
    }

    public function testGet()
    {
        $remote = new Remote();
        $response = $remote->get('http://schemas.learnosity.com/stable/questions/templates');
        $requestPacket = $response->getBody();
        $arr = json_decode($requestPacket, true);

        $this->assertNotEmpty($arr['meta']['status']);
        $this->assertNotEmpty($arr['meta']['timestamp']);
        $this->assertTrue($arr['meta']['status']);
        $this->assertArrayHasKey('data', $arr);

        return $remote;
    }

    /**
     * @depends testPost
     * /
    public function testGetBody($remote)
    {
        //
    }
    */

    /**
     * To avoid warning that no tests found.
     * We're deferring testing data connections, like api or db requests.
     */
   public function test()
   {
        $this->assertTrue(true);
   }
}
