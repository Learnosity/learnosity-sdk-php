<?php

namespace tests\LearnositySdk\Utils;

use LearnositySdk\Utils\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testCheckError()
    {
        $result = Json::checkError();
        $this->assertTrue( is_null($result) || is_string($result) );
    }

    public function dataProviderDecode()
    {
        return [
            ['1', 1],
            ['true', true],
            ['"a"', 'a'],
            [
                '[
                    {
                        "a": "a"
                    },
                    {
                        "b": 1
                    },
                    {
                        "c": true
                    }
                ]',
                [
                    ['a' => 'a'],
                    ['b' => 1],
                    ['c' => true]
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderDecode
     */
    public function testDecode($data, $expectedResult)
    {
        $result = Json::decode($data);
        $this->assertEquals($expectedResult, $result);
    }

    public function dataProviderEncode()
    {
        return [
            [1, '1'],
            [true, 'true'],
            ['a', '"a"'],
            [
                [
                    ['a' => 'a'],
                    ['b' => 1],
                    ['c' => true]
                ],
                '[{"a":"a"},{"b":1},{"c":true}]'
            ]
        ];
    }

    /**
     * @dataProvider dataProviderEncode
     */
    public function testEncode($array, $expectedResult)
    {
        $result = Json::encode($array);
        $this->assertEquals($expectedResult, $result);
    }

    public function dataProviderIsJson()
    {
        return [
            ['12', true],
            ['false', true],
            ['"string"', true],
            ['[a]', false],
            ['["a"]', true],
            ['{a:a}', false],
            ['{"a":"a"}', true],
            ['a', false],
            ['{"a":"a"]', false],
            ['["a"}', false],
            ['[{"a":"a"}, {"b":1}, {"c":true}]', true],
            ['{"meta":{"status":true,"timestamp":1404091707,"request_version":"","schema_version":"develop","records":1}}', true]
        ];
    }

    /**
     * @dataProvider dataProviderIsJson
     */
    public function testIsJson($val, $expectedResult)
    {
        $result = Json::isJson($val);
        $this->assertEquals($expectedResult, $result);
    }
}
