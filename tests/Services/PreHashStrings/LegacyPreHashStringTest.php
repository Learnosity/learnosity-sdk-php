<?php

namespace LearnositySdk\Services\PreHashStrings;

use LearnositySdk\AbstractTestCase;
use LearnositySdk\Exceptions\ValidationException;
use LearnositySdk\Fixtures\ParamsFixture;
use LearnositySdk\Request\Init;

class LegacyPreHashStringTest extends AbstractTestCase
{
    /** @dataProvider preHashStringProvider */
    public function testPreHashString(
        string $service,
        array $security,
        string $secret,
        array|string $request,
        ?string $action,
        bool $v1Compat,
        bool $requestAsString,
        string $expected
    ) {
        $preHashString = new LegacyPreHashString($service, $v1Compat);
        if (is_string($request)) {
            $request = json_decode($request, true);
            $this->assertTrue($request !==  false, 'Cannot decode JSON from string request');
        }
        $result = $preHashString->getPreHashString($security, $request, $action, $v1Compat ? $secret : null);
        $this->assertEquals($expected, $result);
    }

    /** @returns array <
     *   string $service
     *   array $security
     *   string $secret
     *   array|string $request
     *   ?string $action
     *   bool $v1Compat
     *   bool $requestAsString
     *   string $expected
     * > */
    public function preHashStringProvider()
    {
        Init::disableTelemetry();
        $testCases = [];

        $v1Compat = false;
        $requestAsString = true;

        foreach (LegacyPreHashString::getSupportedServices() as $service) {
            $camelCaseService = str_replace(
                ' ',
                '',
                ucwords(str_replace('-', ' ', $service))
            );
            $getParams = 'getWorking' . $camelCaseService . 'ApiParams';
            $tc = array_merge(
                ParamsFixture::$getParams(true),
                [
                    'v1Compat' => $v1Compat,
                    'requestAsString' => $requestAsString,
                ]
            );

            $init = new Init($service, $tc['security'], $tc['secret'], $tc['request'], $tc['action']);
            $tc['expected'] = $init->generatePreHashString();

            $testCases["api-{$service}"] = $tc;
        }

        Init::enableTelemetry();
        return $testCases;
    }
}
