<?php

namespace tests\LearnositySdk\Request;

use LearnositySdk\Request\Init;

class InitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * WARNING: RemoteTest is also using this params
     *
     * @param  boolean $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingDataApiParams($assoc = false)
    {
        $service = 'data';
        $security = array(
            'consumer_key' => 'yis0TYCu7U9V4o7M',
            'domain'       => 'localhost'
        );
        $secret = '74c5fd430cf1242a527f6223aebd42d30464be22';
        $request = array(
            'limit' => 100
        );
        $action = 'get';

        if($assoc) {
            return array(
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action
            );
        } else {
            return array(
                $service,
                $security,
                $secret,
                $request,
                $action
            );
        }
    }

    public function dataProviderGenerateSignature()
    {
        list($service, $security, $secret, $request, $action) = self::getWorkingDataApiParams();
        $security['timestamp'] = '20140626-0528';

        return [
            [
                'e1eae0b86148df69173cb3b824275ea73c9c93967f7d17d6957fcdd299c8a4fe',
                new Init($service, $security, $secret, $request, $action)
            ],
            [
                '18e5416041a13f95681f747222ca7bdaaebde057f4f222083881cd0ad6282c38',
                new Init($service, $security, $secret, $request, 'post')
            ]
        ];
    }

    /**
     * @param  string $expectedResult
     * @param  Init   $initObject
     *
     * @dataProvider dataProviderGenerateSignature
     */
    public function testGenerateSignature($expectedResult, $initObject)
    {
        $this->assertEquals($expectedResult, $initObject->generateSignature());
    }

    public function dataProviderGenerate()
    {
        list($service, $security, $secret, $request, $action) = self::getWorkingDataApiParams();
        $security['timestamp'] = '20140626-0528';

        return [
            [
                [
                    'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"e1eae0b86148df69173cb3b824275ea73c9c93967f7d17d6957fcdd299c8a4fe"}',
                    'request'  => '{"limit":100}',
                    'action'   => 'get'
                ],
                new Init($service, $security, $secret, $request, $action)
            ],
            [
                [
                    'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"18e5416041a13f95681f747222ca7bdaaebde057f4f222083881cd0ad6282c38"}',
                    'request'  => '{"limit":100}',
                    'action'   => 'post'
                ],
                new Init($service, $security, $secret, $request, 'post')
            ]
        ];
    }

    /**
     * @param  string $expectedResult
     * @param  Init   $initObject
     *
     * @dataProvider dataProviderGenerate
     */
    public function testGenerate($expectedResult, $initObject)
    {
        $generated = $initObject->generate();

        if (is_array($expectedResult)) {
            ksort($expectedResult);
        }

        if (is_array($generated)) {
            ksort($generated);
        }

        $this->assertEquals($expectedResult, $generated);
    }

    public function dataProviderConstructor()
    {
        list($service, $security, $secret, $request, $action) = self::getWorkingDataApiParams();

        $wrongSecurity = $security;
        $wrongSecurity['wrongParam'] = '';

        return [
            [$service, $security, $secret, $request, $action, new Init($service, $security, $secret, $request, $action)],
            ['', $security, $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The `service` argument wasn\'t found or was empty'],
            ['wrongService', $security, $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The service provided (wrongService) is not valid'],
            [$service, '', $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The security packet must be an array'],
            [$service, $wrongSecurity, $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'Invalid key found in the security packet: wrongParam'],
            ['questions', $security, $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'If using the question api, a user id needs to be specified'],
            [$service, $security, 25, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The `secret` argument must be a valid string'],
            [$service, $security, '', $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The `secret` argument must be a valid string'],
            [$service, $security, $secret, 25, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The request packet must be an array'],
            [$service, $security, $secret, $request, 25, null, '\LearnositySdk\Exceptions\ValidationException', 'The action parameter must be a string']
        ];
    }

    /**
     * @param  array  $params
     * @param  string $expectedException
     * @param  string $expectedExceptionMessage
     *
     * @dataProvider dataProviderConstructor
     */
    public function testConstructor($service, $securityPacket, $secret, $requestPacket = null, $action = null,
        $expectedResult, $expectedException = null, $expectedExceptionMessage = null
    ) {
        if (!empty($expectedException)) {
            $this->setExpectedException($expectedException, $expectedExceptionMessage);
        }

        $init = new Init($service, $securityPacket, $secret, $requestPacket, $action);

        $this->assertEquals($expectedResult, $init);
    }

    public function dataProviderValidate()
    {
        return $this->dataProviderConstructor();
    }

    /**
     * The same as testConstructor, because validate is called by constructor anyway.
     * So this is only for right coverage.
     *
     * @param  array  $params
     * @param  string $expectedException
     * @param  string $expectedExceptionMessage
     *
     * @dataProvider dataProviderConstructor
     */
    public function testValidate($service, $securityPacket, $secret, $requestPacket = null, $action = null,
        $expectedResult, $expectedException = null, $expectedExceptionMessage = null
    ) {
        if (!empty($expectedException)) {
            $this->setExpectedException($expectedException, $expectedExceptionMessage);
        }

        $init = new Init($service, $securityPacket, $secret, $requestPacket, $action);

        $this->assertEquals($expectedResult, $init);
    }
}
