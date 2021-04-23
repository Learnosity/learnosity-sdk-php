<?php

namespace LearnositySdk\Request;

use LearnositySdk\AbstractTestCase;

class RemoteTest extends AbstractTestCase
{
    public function testPost()
    {
        list($service, $security, $secret, $request, $action) = InitTest::getWorkingDataApiParams();
        unset($security['timestamp']);
        $init = new Init($service, $security, $secret, $request, $action);

        $url = $this->buildBaseDataUrl() . '/sessions/statuses';

        $remote = new Remote();
        $ret = $remote->post($url, $init->generate());

        $this->assertInstanceOf('LearnositySdk\Request\Remote', $ret);

        $body = $remote->getBody();
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
        $ret = $remote->get($this->buildBaseSchemasUrl() . '/questions/templates');
        $body = $ret->getBody();
        $arr = json_decode($body, true);

        $this->assertNotEmpty($arr['meta']['status']);
        $this->assertNotEmpty($arr['meta']['timestamp']);
        $this->assertTrue($arr['meta']['status']);
        $this->assertArrayHasKey('data', $arr);

        return $ret;
    }

    private function buildBaseDataUrl()
    {
        $versionPath = 'v1';
        if (isset($_SERVER['ENV']) && $_SERVER['ENV'] != 'prod') {
            $versionPath = 'latest';
        } elseif (isset($_SERVER['VER'])) {
            $versionPath = $_SERVER['VER'];
        }

        return 'https://data' . $this->buildBaseDomain() . '/' . $versionPath;
    }

    private function buildBaseSchemasUrl()
    {
        return 'https://schemas' . $this->buildBaseDomain() . '/latest';
    }

    private function buildBaseDomain()
    {
        $envDomain = '';
        $regionDomain = '.learnosity.com';
        if (isset($_SERVER['ENV']) && $_SERVER['ENV'] != 'prod') {
            $envDomain = '.' . $_SERVER['ENV'];
        } elseif (isset($_SERVER['REGION'])) {
            $regionDomain = $_SERVER['REGION'];
        }
        return $envDomain . $regionDomain;
    }
}
