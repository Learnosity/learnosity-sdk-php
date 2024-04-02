<?php

namespace LearnositySdk\Request;

use LearnositySdk\AbstractTestCase;
use LearnositySdk\Exceptions\ValidationException;
use LearnositySdk\Services\SignatureFactory;

class InitTest extends AbstractTestCase
{

    /**
     * @var \LearnositySdk\Services\SignatureFactory
     */
    private $signatureFactory;

    public function setUp(): void
    {
        $this->signatureFactory = new SignatureFactory();
    }

    /*
     * Tests
     */

    /**
     * @dataProvider constructorProvider
     */
    public function testConstructor(
        $service,
        $securityPacket,
        $secret,
        $requestPacket = null,
        $action = null,
        $expectedResult = null,
        $expectedException = null,
        $expectedExceptionMessage = null
    ) {
        if (!empty($expectedException)) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $init = new Init($service, $securityPacket, $secret, $requestPacket, $action, $this->signatureFactory);

        $this->assertEquals($expectedResult, $init);
    }

    /**
     * @dataProvider generateWithMetaProvider
     */
    public function testGenerateWithMeta(string $pathToMeta, $generated)
    {
        $pathParts = explode('.', $pathToMeta);
        // in case of Author API, Assess API, Events API, Items API, Questions API and Reports API
        // generated value is a string, and therefore needs to be decoded
        $temp = is_string($generated) ? json_decode($generated, true) : $generated;

        for ($i = 0; $i < count($pathParts) && isset($temp[$pathParts[$i]]); $i++) {
            // in case of Data API, request is a string, and therefore needs to be decoded
            $temp = is_string($temp[$pathParts[$i]]) ? json_decode($temp[$pathParts[$i]], true) : $temp[$pathParts[$i]];
        }

        $this->assertEquals($i, count($pathParts));
        $this->assertNotEmpty($temp);
    }

    /**
     * @dataProvider generateSignatureProvider
     */
    public function testGenerateSignature(string $expectedResult, Init $initObject)
    {
        $this->assertEquals($expectedResult, $initObject->generateSignature());
    }

    /**
     * @dataProvider generateProvider
     */
    public function testGenerate($expectedResult, Init $initObject)
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

    public function testNullRequestPacketGeneratesValidInit()
    {
        list($service, $security, $secret) = static::getWorkingDataApiParams();
        $initObject = new Init($service, $security, $secret, null, null, $this->signatureFactory);
        $this->assertInstanceOf(Init::class, $initObject);
    }

    public function testEmptyArrayRequestPacketGeneratesValidInit()
    {
        list($service, $security, $secret) = static::getWorkingDataApiParams();
        $initObject = new Init($service, $security, $secret, [], null, $this->signatureFactory);
        $this->assertInstanceOf(Init::class, $initObject);
    }

    public function testEmptyStringGeneratesValidInit()
    {
        list($service, $security, $secret) = static::getWorkingDataApiParams();
        $initObject = new Init($service, $security, $secret, "", null, $this->signatureFactory);
        $this->assertInstanceOf(Init::class, $initObject);
    }

    public function testNullRequestPacketAndActionGeneratesValidInit()
    {
        list($service, $security, $secret) = static::getWorkingDataApiParams();
        $initObject = new Init($service, $security, $secret, null, null, $this->signatureFactory);
        $this->assertInstanceOf(Init::class, $initObject);
    }

    public function testMetaWithTelemetryOnlyAddsSdkProp()
    {
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();

        $initObject = new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory);
        $generatedObject = json_decode($initObject->generate());

        // when telemetry is enabled, if the $request has no meta field,
        // then the meta of the generated object has the sdk field only
        $this->assertObjectHasProperty('meta', $generatedObject);
        $this->assertObjectHasProperty('sdk', $generatedObject->meta);
        $this->assertEquals(1, count((array) $generatedObject->meta));
    }

    public function testRequestWithTelemetryPreservesOtherMetaProps()
    {
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();

        // add meta field to the $request
        $request['meta'] = $this->getMetaField();

        // generate a new $initObject using the updated $request
        $initObject = new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory);
        $generatedObject = json_decode($initObject->generate());

        // when telemetry is enabled, if the request has a meta field,
        // then the generated object's meta property should be present
        $this->assertObjectHasProperty('meta', $generatedObject);

        // each key of the meta array should be present in the generated object's meta field as a property
        foreach (array_keys($request['meta']) as $propName) {
            $this->assertObjectHasProperty($propName, $generatedObject->meta);
        }

        // the generated object should have sdk property, too
        $this->assertEquals(count($request['meta']) + 1, count((array) $generatedObject->meta));
        $this->assertObjectHasProperty('sdk', $generatedObject->meta);
    }

    public function testRequestWithoutTelemetryPreservesEmptyMeta()
    {
        Init::disableTelemetry();
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();

        $initObject = new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory);
        $generatedObject = json_decode($initObject->generate());

        // when telemetry is disabled, if the meta field of the $request is empty,
        // then the meta of the generated object should also be empty
        $this->assertObjectNotHasProperty('meta', $generatedObject);
        Init::enableTelemetry();
    }

    public function testRequestWithoutTelemetryPreservesFilledMeta()
    {
        Init::disableTelemetry();
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();

        // add meta field to the $request
        $request['meta'] = $this->getMetaField();

        $initObject = new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory);
        $generatedObject = json_decode($initObject->generate());

        // when telemetry is disabled, if the meta field of the $request has properties,
        // then the meta of the generated object will also contain these properties, and nothing else
        $this->assertObjectHasProperty('meta', $generatedObject);

        foreach (array_keys($request['meta']) as $propName) {
            $this->assertObjectHasProperty($propName, $generatedObject->meta);
        }

        $this->assertEquals(count($request['meta']), count((array) $generatedObject->meta));
        $this->assertObjectNotHasProperty('sdk', $generatedObject->meta);

        Init::enableTelemetry();
    }

    /*
     * Pseudo fixtures for parameters
     */

    /**
     * @param  bool $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingAssessApiParams(bool $assoc = false): array
    {
        $service = 'assess';
        $security = static::getSecurity();
        // Needed to initialise Questions API
        $security['user_id'] = '$ANONYMIZED_USER_ID';
        $secret = static::TEST_CONSUMER_SECRET;
        $request = [
            "items" => [
                [
                    "content" => '<span class="learnosity-response question-demoscience1234"></span>',
                    "response_ids" => [
                        "demoscience1234"
                    ],
                    "workflow" => "",
                    "reference" => "question-demoscience1"
                ],
                [
                    "content" => '<span class="learnosity-response question-demoscience5678"></span>',
                    "response_ids" => [
                        "demoscience5678"
                    ],
                    "workflow" => "",
                    "reference" => "question-demoscience2"
                ]
            ],
            "ui_style" =>"horizontal",
            "name" => "Demo (2 questions)",
            "state" => "initial",
            "metadata" => [],
            "navigation" => [
                "show_next" => true,
                "toc" => true,
                "show_submit" => true,
                "show_save" => false,
                "show_prev" => true,
                "show_title" => true,
                "show_intro" => true,
            ],
            "time" => [
                "max_time" => 600,
                "limit_type" => "soft",
                "show_pause" => true,
                "warning_time" => 60,
                "show_time" => true
            ],
            "configuration" => [
                "onsubmit_redirect_url" => "/assessment/",
                "onsave_redirect_url" => "/assessment/",
                "idle_timeout" => true,
                "questionsApiVersion" => "v2"
            ],
            "questionsApiActivity" => [
                "user_id" => '$ANONYMIZED_USER_ID',
                "type" => "submit_practice",
                "state" => "initial",
                "id" => "assessdemo",
                "name" => "Assess API - Demo",
                "questions" => [
                    [
                        "response_id" => "demoscience1234",
                        "type" => "sortlist",
                        "description" => "In this question, the student needs to sort the events, chronologically earliest to latest.",
                        "list" => ["Russian Revolution", "Discovery of the Americas", "Storming of the Bastille", "Battle of Plataea", "Founding of Rome", "First Crusade"],
                        "instant_feedback" => true,
                        "feedback_attempts" => 2,
                        "validation" => [
                            "valid_response" => [4, 3, 5, 1, 2, 0],
                            "valid_score" => 1,
                            "partial_scoring" => true,
                            "penalty_score" => -1
                        ]
                    ],
                    [
                        "response_id" => "demoscience5678",
                        "type" => "highlight",
                        "description" => "The student needs to mark one of the flowers anthers in the image.",
                        "img_src" => "http://www.learnosity.com/static/img/flower.jpg",
                        "line_color" => "rgb(255, 20, 0)",
                        "line_width" => "4"
                    ]
                ]
            ],
            "type" => "activity"
        ];
        $action = null;

        if ($assoc) {
            return [
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action,
            ];
        } else {
            return [
                $service,
                $security,
                $secret,
                $request,
                $action,
            ];
        }
    }

    /**
     * @param  bool $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingAuthorApiParams(bool $assoc = false): array
    {
        $service = 'author';
        $security = static::getSecurity();
        $secret = static::TEST_CONSUMER_SECRET;
        $request = [
            "mode" => "item_list",
            "config" => [
                "item_list" => [
                    "item" => [
                        "status" => true
                    ]
                ]
            ],
            "user" => [
                "id" => "walterwhite",
                "firstname" => "walter",
                "lastname" => "white"
            ]
        ];
        $action = null;

        if ($assoc) {
            return [
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action,
            ];
        } else {
            return [
                $service,
                $security,
                $secret,
                $request,
                $action,
            ];
        }
    }

    /**
     * @param  bool $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingAuthorAideApiParams(bool $assoc = false): array
    {
        $service = 'authoraide';
        $security = static::getSecurity();
        $secret = static::TEST_CONSUMER_SECRET;
        $request = [
            "config" => [
                "test-attribute" => "test"
            ],
            "user" => [
                "id" => "walterwhite",
                "firstname" => "walter",
                "lastname" => "white"
            ]
        ];
        $action = null;

        if ($assoc) {
            return [
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action,
            ];
        } else {
            return [
                $service,
                $security,
                $secret,
                $request,
                $action,
            ];
        }
    }

    /**
     * WARNING: RemoteTest is also using this params
     *
     * @param  bool $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingDataApiParams(bool $assoc = false): array
    {
        $service = 'data';
        $security = static::getSecurity();
        $secret = static::TEST_CONSUMER_SECRET;
        $request = [
            'limit' => 100,
        ];
        $action = 'get';

        if ($assoc) {
            return [
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action,
            ];
        } else {
            return [
                $service,
                $security,
                $secret,
                $request,
                $action
            ];
        }
    }

    /**
     * @param  bool $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingEventsApiParams(bool $assoc = false): array
    {
        $service = 'events';
        $security = static::getSecurity();
        $secret = static::TEST_CONSUMER_SECRET;
        $request = [
            'users' => [
                '$ANONYMIZED_USER_ID_1' => '$ANONYMIZED_USER_ID_1_NAME',
                '$ANONYMIZED_USER_ID_2' => '$ANONYMIZED_USER_ID_2_NAME',
                '$ANONYMIZED_USER_ID_3' => '$ANONYMIZED_USER_ID_3_NAME',
                '$ANONYMIZED_USER_ID_4' => '$ANONYMIZED_USER_ID_4_NAME'
            ]
        ];
        $action = null;

        if ($assoc) {
            return [
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action,
            ];
        } else {
            return [
                $service,
                $security,
                $secret,
                $request,
                $action,
            ];
        }
    }

    /**
     * @param  bool $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingItemsApiParams(bool $assoc = false): array
    {
        $service = 'items';
        $security = static::getSecurity();
        $secret = static::TEST_CONSUMER_SECRET;
        $request = [
            'user_id' => '$ANONYMIZED_USER_ID',
            'rendering_type' => 'assess',
            'name' => 'Items API demo - assess activity demo',
            'state' => 'initial',
            'activity_id' => 'items_assess_demo',
            'session_id' => 'demo_session_uuid',
            'type' => 'submit_practice',
            'config' => [
                'configuration' => [
                    'responsive_regions' => true
                ],
                'navigation' => [
                    'scrolling_indicator' => true
                ],
                'regions' => 'main',
                'time' => [
                    'show_pause' => true,
                    'max_time' => 300
                ],
                'title' => 'ItemsAPI Assess Isolation Demo',
                'subtitle' => 'Testing Subtitle Text'
            ],
            'items' => [
                'Demo3'
            ]
        ];
        $action = null;

        if ($assoc) {
            return [
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action,
            ];
        } else {
            return [
                $service,
                $security,
                $secret,
                $request,
                $action,
            ];
        }
    }

    /**
     * @param  bool $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingQuestionsApiParams(bool $assoc = false): array
    {
        $service = 'questions';
        $security = static::getSecurity();
        $security['user_id'] = '$ANONYMIZED_USER_ID';
        $secret = static::TEST_CONSUMER_SECRET;
        $request = [
            'type'      => 'local_practice',
            'state'     => 'initial',
            'questions' => [
                [
                    'response_id'        => '60005',
                    'type'               => 'association',
                    'stimulus'           => 'Match the cities to the parent nation.',
                    'stimulus_list'      => ['London', 'Dublin', 'Paris', 'Sydney'],
                    'possible_responses' => ['Australia', 'France', 'Ireland', 'England'],
                    'validation' => [
                        'valid_responses' => [
                            ['England'], ['Ireland'], ['France'], ['Australia']
                        ]
                    ]
                ]
            ]
        ];
        $action = null;

        if ($assoc) {
            return [
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action,
            ];
        } else {
            return [
                $service,
                $security,
                $secret,
                $request,
                $action,
            ];
        }
    }

    /**
     * @param  bool $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingReportsApiParams(bool $assoc = false): array
    {
        $service = 'reports';
        $security = static::getSecurity();
        $secret = static::TEST_CONSUMER_SECRET;
        $request = [
           "reports" => [
               [
                   "id" => "report-1",
                   "type" => "sessions-summary",
                   "user_id" => '$ANONYMIZED_USER_ID',
                   "session_ids" => [
                       "AC023456-2C73-44DC-82DA28894FCBC3BF"
                   ]
               ]
           ]
        ];
        $action = null;

        if ($assoc) {
            return [
                'service' => $service,
                'security' => $security,
                'secret' => $secret,
                'request' => $request,
                'action' => $action,
            ];
        } else {
            return [
                $service,
                $security,
                $secret,
                $request,
                $action,
            ];
        }
    }

    /**
     *
     * @return array
     */
    public function getMetaField(): array
    {
        return [
            "test_key_string" => "test-string",
            "test_key_integer" => 12345,
            "test_key_boolean" => true,
        ];
    }

    /*
     * Data providers
     */

    public function constructorProvider(): array
    {
        list($service, $security, $secret, $request, $action) = static::getWorkingDataApiParams();

        $wrongSecurity = $security;
        $wrongSecurity['wrongParam'] = '';

        return [
            [$service, $security, $secret, $request, $action, new Init($service, $security, $secret, $request, $action, $this->signatureFactory)],
            ['', $security, $secret, $request, $action, null, ValidationException::class, 'The `service` argument wasn\'t found or was empty'],
            ['wrongService', $security, $secret, $request, $action, null, ValidationException::class, 'The service provided (wrongService) is not valid'],
            [$service, '', $secret, $request, $action, null, ValidationException::class, 'The security packet must be an array or a valid JSON string'],
            [$service, null, $secret, $request, $action, null, ValidationException::class, 'The security packet must be an array or a valid JSON string'],
            [$service, '', $secret, $request, $action, null, ValidationException::class, 'The security packet must be an array or a valid JSON string'],
            [$service, $security, '', $request, $action, null, ValidationException::class, 'The `secret` argument must be a valid string'],
            [$service, $wrongSecurity, $secret, $request, $action, null, ValidationException::class, 'Invalid key found in the security packet: wrongParam'],
            ['questions', $security, $secret, $request, $action, null, ValidationException::class, 'Questions API requires a `user_id` in the security packet'],
            [$service, $security, $secret, 25, $action, null, ValidationException::class, 'The request packet must be an array or a valid JSON string'],
        ];
    }

    public function generateProvider(): array
    {
        // We disable telemetry to be able to reliably test signature generation. Added telemetry
        // will differ on each platform tests would be run, and therefore fail.
        Init::disableTelemetry();

        $testCases = [];

        /* Author */
        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorApiParams();
        $authorApiV1 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861"},"request":{"mode":"item_list","config":{"item_list":{"item":{"status":true}}},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $authorApiV1;

        $authorApiV2 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861"},"request":{"mode":"item_list","config":{"item_list":{"item":{"status":true}}},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $authorApiV2;

        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorAideApiParams();
        $authorAide = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$f2ce1da2fdead193d53ab954b8a3660548ed9b0e3ce60599d751130deba7a138"},"request":{"config":{"test-attribute":"test"},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $authorAide;

        /* Assess */
        list($service, $security, $secret, $request, $action) = static::getWorkingAssessApiParams();
        $assessApiV1 = [
            '{"items":[{"content":"<span class=\"learnosity-response question-demoscience1234\"></span>","response_ids":["demoscience1234"],"workflow":"","reference":"question-demoscience1"},{"content":"<span class=\"learnosity-response question-demoscience5678\"></span>","response_ids":["demoscience5678"],"workflow":"","reference":"question-demoscience2"}],"ui_style":"horizontal","name":"Demo (2 questions)","state":"initial","metadata":[],"navigation":{"show_next":true,"toc":true,"show_submit":true,"show_save":false,"show_prev":true,"show_title":true,"show_intro":true},"time":{"max_time":600,"limit_type":"soft","show_pause":true,"warning_time":60,"show_time":true},"configuration":{"onsubmit_redirect_url":"/assessment/","onsave_redirect_url":"/assessment/","idle_timeout":true,"questionsApiVersion":"v2"},"questionsApiActivity":{"user_id":"$ANONYMIZED_USER_ID","type":"submit_practice","state":"initial","id":"assessdemo","name":"Assess API - Demo","questions":[{"response_id":"demoscience1234","type":"sortlist","description":"In this question, the student needs to sort the events, chronologically earliest to latest.","list":["Russian Revolution","Discovery of the Americas","Storming of the Bastille","Battle of Plataea","Founding of Rome","First Crusade"],"instant_feedback":true,"feedback_attempts":2,"validation":{"valid_response":[4,3,5,1,2,0],"valid_score":1,"partial_scoring":true,"penalty_score":-1}},{"response_id":"demoscience5678","type":"highlight","description":"The student needs to mark one of the flowers anthers in the image.","img_src":"http://www.learnosity.com/static/img/flower.jpg","line_color":"rgb(255, 20, 0)","line_width":"4"}],"consumer_key":"yis0TYCu7U9V4o7M","timestamp":"20140626-0528","signature":"$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8"},"type":"activity"}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $assessApiV1;

        $assessApiV2 = [
            '{"items":[{"content":"<span class=\"learnosity-response question-demoscience1234\"></span>","response_ids":["demoscience1234"],"workflow":"","reference":"question-demoscience1"},{"content":"<span class=\"learnosity-response question-demoscience5678\"></span>","response_ids":["demoscience5678"],"workflow":"","reference":"question-demoscience2"}],"ui_style":"horizontal","name":"Demo (2 questions)","state":"initial","metadata":[],"navigation":{"show_next":true,"toc":true,"show_submit":true,"show_save":false,"show_prev":true,"show_title":true,"show_intro":true},"time":{"max_time":600,"limit_type":"soft","show_pause":true,"warning_time":60,"show_time":true},"configuration":{"onsubmit_redirect_url":"/assessment/","onsave_redirect_url":"/assessment/","idle_timeout":true,"questionsApiVersion":"v2"},"questionsApiActivity":{"user_id":"$ANONYMIZED_USER_ID","type":"submit_practice","state":"initial","id":"assessdemo","name":"Assess API - Demo","questions":[{"response_id":"demoscience1234","type":"sortlist","description":"In this question, the student needs to sort the events, chronologically earliest to latest.","list":["Russian Revolution","Discovery of the Americas","Storming of the Bastille","Battle of Plataea","Founding of Rome","First Crusade"],"instant_feedback":true,"feedback_attempts":2,"validation":{"valid_response":[4,3,5,1,2,0],"valid_score":1,"partial_scoring":true,"penalty_score":-1}},{"response_id":"demoscience5678","type":"highlight","description":"The student needs to mark one of the flowers anthers in the image.","img_src":"http://www.learnosity.com/static/img/flower.jpg","line_color":"rgb(255, 20, 0)","line_width":"4"}],"consumer_key":"yis0TYCu7U9V4o7M","timestamp":"20140626-0528","signature":"$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8"},"type":"activity"}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $assessApiV2;

        /* Data */
        list($service, $security, $secret, $request, $action) = static::getWorkingDataApiParams();
        $security['timestamp'] = '20140626-0528';
        $dataApiGetV1 = [
            [
                'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$e19c8a62fba81ef6baf2731e2ab0512feaf573ca5ca5929c2ee9a77303d2e197"}',
                'request'  => '{"limit":100}',
                'action'   => 'get'
            ],
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $dataApiGetV1;

        $dataApiGetV2 = [
            [
                'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$e19c8a62fba81ef6baf2731e2ab0512feaf573ca5ca5929c2ee9a77303d2e197"}',
                'request'  => '{"limit":100}',
                'action'   => 'get'
            ],
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $dataApiGetV2;

        $dataApiPostV1 = [
            [
                'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$9d1971fb9ac51482f7e73dcf87fc029d4a3dfffa05314f71af9d89fb3c2bcf16"}',
                'request'  => '{"limit":100}',
                'action'   => 'post'
            ],
            new Init($service, $security, $secret, $request, 'post', $this->signatureFactory)
        ];
        $testCases[] = $dataApiPostV1;

        $dataApiPostV2 = [
            [
                'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$9d1971fb9ac51482f7e73dcf87fc029d4a3dfffa05314f71af9d89fb3c2bcf16"}',
                'request'  => '{"limit":100}',
                'action'   => 'post'
            ],
            new Init($service, $security, $secret, $request, 'post', $this->signatureFactory, '02')
        ];
        $testCases[] = $dataApiPostV2;

        /* Events */
        list($service, $security, $secret, $request, $action) = static::getWorkingEventsApiParams();
        $eventsApiExpectedV1 = '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$5c3160dbb9ab4d01774b5c2fc3b01a35ce4f9709c84571c27dfe333d1ca9d349"},"config":{"users":{"$ANONYMIZED_USER_ID_1":"64ccf06154cf4133624372459ebcccb8b2f8bd7458a73df681acef4e742e175c","$ANONYMIZED_USER_ID_2":"7fa4d6ef8926add8b6411123fce916367250a6a99f50ab8ec39c99d768377adb","$ANONYMIZED_USER_ID_3":"3d5b26843da9192319036b67f8c5cc26e1e1763811270ba164665d0027296952","$ANONYMIZED_USER_ID_4":"3b6ac78f60f3e3eb7a85cec8b48bdca0f590f959e0a87a9c4222898678bd50c8"}}}';

        $eventsApiV1 = [
            $eventsApiExpectedV1,
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $eventsApiV1;

        $eventsApiExpectedV2 = '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$5c3160dbb9ab4d01774b5c2fc3b01a35ce4f9709c84571c27dfe333d1ca9d349"},"config":{"users":{"$ANONYMIZED_USER_ID_1":"64ccf06154cf4133624372459ebcccb8b2f8bd7458a73df681acef4e742e175c","$ANONYMIZED_USER_ID_2":"7fa4d6ef8926add8b6411123fce916367250a6a99f50ab8ec39c99d768377adb","$ANONYMIZED_USER_ID_3":"3d5b26843da9192319036b67f8c5cc26e1e1763811270ba164665d0027296952","$ANONYMIZED_USER_ID_4":"3b6ac78f60f3e3eb7a85cec8b48bdca0f590f959e0a87a9c4222898678bd50c8"}}}';
        $eventsApiV2 = [
            $eventsApiExpectedV2,
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $eventsApiV2;

        /* Items */
        list($service, $security, $secret, $request, $action) = static::getWorkingItemsApiParams();
        $itemsApiV1 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9"},"request":{"user_id":"$ANONYMIZED_USER_ID","rendering_type":"assess","name":"Items API demo - assess activity demo","state":"initial","activity_id":"items_assess_demo","session_id":"demo_session_uuid","type":"submit_practice","config":{"configuration":{"responsive_regions":true},"navigation":{"scrolling_indicator":true},"regions":"main","time":{"show_pause":true,"max_time":300},"title":"ItemsAPI Assess Isolation Demo","subtitle":"Testing Subtitle Text"},"items":["Demo3"]}}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $itemsApiV1;

        $itemsApiV2 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9"},"request":{"user_id":"$ANONYMIZED_USER_ID","rendering_type":"assess","name":"Items API demo - assess activity demo","state":"initial","activity_id":"items_assess_demo","session_id":"demo_session_uuid","type":"submit_practice","config":{"configuration":{"responsive_regions":true},"navigation":{"scrolling_indicator":true},"regions":"main","time":{"show_pause":true,"max_time":300},"title":"ItemsAPI Assess Isolation Demo","subtitle":"Testing Subtitle Text"},"items":["Demo3"]}}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $itemsApiV2;

        /* Questions */
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();
        $questionsApiV1 = [
            '{"consumer_key":"yis0TYCu7U9V4o7M","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8","type":"local_practice","state":"initial","questions":[{"response_id":"60005","type":"association","stimulus":"Match the cities to the parent nation.","stimulus_list":["London","Dublin","Paris","Sydney"],"possible_responses":["Australia","France","Ireland","England"],"validation":{"valid_responses":[["England"],["Ireland"],["France"],["Australia"]]}}]}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $questionsApiV1;

        $questionsApiV2 = [
            '{"consumer_key":"yis0TYCu7U9V4o7M","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8","type":"local_practice","state":"initial","questions":[{"response_id":"60005","type":"association","stimulus":"Match the cities to the parent nation.","stimulus_list":["London","Dublin","Paris","Sydney"],"possible_responses":["Australia","France","Ireland","England"],"validation":{"valid_responses":[["England"],["Ireland"],["France"],["Australia"]]}}]}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $questionsApiV2;

        /* Reports */
        list($service, $security, $secret, $request, $action) = static::getWorkingReportsApiParams();
        $reportsApiV1 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$8e0069e7aa8058b47509f35be236c53fa1a878c64b12589fd42f48b568f6ac84"},"request":{"reports":[{"id":"report-1","type":"sessions-summary","user_id":"$ANONYMIZED_USER_ID","session_ids":["AC023456-2C73-44DC-82DA28894FCBC3BF"]}]}}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $reportsApiV1;

        $reportsApiV2 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$8e0069e7aa8058b47509f35be236c53fa1a878c64b12589fd42f48b568f6ac84"},"request":{"reports":[{"id":"report-1","type":"sessions-summary","user_id":"$ANONYMIZED_USER_ID","session_ids":["AC023456-2C73-44DC-82DA28894FCBC3BF"]}]}}',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $reportsApiV2;

        /* Passing request as string */
        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorApiParams();
        $authorApiAsStringV1 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861"},"request":"{\"mode\":\"item_list\",\"config\":{\"item_list\":{\"item\":{\"status\":true}}},\"user\":{\"id\":\"walterwhite\",\"firstname\":\"walter\",\"lastname\":\"white\"}}"}',
            new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory)
        ];
        $testCases[] = $authorApiAsStringV1;

        $authorApiAsStringV2 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861"},"request":"{\"mode\":\"item_list\",\"config\":{\"item_list\":{\"item\":{\"status\":true}}},\"user\":{\"id\":\"walterwhite\",\"firstname\":\"walter\",\"lastname\":\"white\"}}"}',
            new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $authorApiAsStringV2;

        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorAideApiParams();
        $authorAideApiAsString = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$f2ce1da2fdead193d53ab954b8a3660548ed9b0e3ce60599d751130deba7a138"},"request":"{\"config\":{\"test-attribute\":\"test\"},\"user\":{\"id\":\"walterwhite\",\"firstname\":\"walter\",\"lastname\":\"white\"}}"}',
            new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory)
        ];
        $testCases[] = $authorAideApiAsString;

        /* Items */
        list($service, $security, $secret, $request, $action) = static::getWorkingItemsApiParams();
        $itemsApiAsStringV1 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9"},"request":"{\"user_id\":\"$ANONYMIZED_USER_ID\",\"rendering_type\":\"assess\",\"name\":\"Items API demo - assess activity demo\",\"state\":\"initial\",\"activity_id\":\"items_assess_demo\",\"session_id\":\"demo_session_uuid\",\"type\":\"submit_practice\",\"config\":{\"configuration\":{\"responsive_regions\":true},\"navigation\":{\"scrolling_indicator\":true},\"regions\":\"main\",\"time\":{\"show_pause\":true,\"max_time\":300},\"title\":\"ItemsAPI Assess Isolation Demo\",\"subtitle\":\"Testing Subtitle Text\"},\"items\":[\"Demo3\"]}"}',
            new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory)
        ];
        $testCases[] = $itemsApiAsStringV1;

        $itemsApiAsStringV2 = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9"},"request":"{\"user_id\":\"$ANONYMIZED_USER_ID\",\"rendering_type\":\"assess\",\"name\":\"Items API demo - assess activity demo\",\"state\":\"initial\",\"activity_id\":\"items_assess_demo\",\"session_id\":\"demo_session_uuid\",\"type\":\"submit_practice\",\"config\":{\"configuration\":{\"responsive_regions\":true},\"navigation\":{\"scrolling_indicator\":true},\"regions\":\"main\",\"time\":{\"show_pause\":true,\"max_time\":300},\"title\":\"ItemsAPI Assess Isolation Demo\",\"subtitle\":\"Testing Subtitle Text\"},\"items\":[\"Demo3\"]}"}',
            new Init($service, $security, $secret, json_encode($request), $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $itemsApiAsStringV2;

        Init::enableTelemetry();

        return $testCases;
    }

    public function generateSignatureProvider(): array
    {
        // We disable telemetry to be able to reliably test signature generation. Added telemetry
        // will differ on each platform tests would be run, and therefore fail.
        Init::disableTelemetry();

        $testCases = [];

        /* Author */
        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorApiParams();
        $authorApiV1 = [
            '$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $authorApiV1;

        $authorApiV2 = [
            '$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $authorApiV2;

        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorAideApiParams();
        $authorAide = [
            '$02$f2ce1da2fdead193d53ab954b8a3660548ed9b0e3ce60599d751130deba7a138',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $authorAide;

        /* Assess */
        list($service, $security, $secret, $request, $action) = static::getWorkingAssessApiParams();
        $assessApiV1 = [
            '$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $assessApiV1;

        $assessApiV2 = [
            '$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $assessApiV2;

        /* Data */
        list($service, $security, $secret, $request, $action) = static::getWorkingDataApiParams();
        $securityExpires = $security;
        $securityExpires['expires'] = '20160621-1716';

        $dataApiV1 = [
            '$02$e19c8a62fba81ef6baf2731e2ab0512feaf573ca5ca5929c2ee9a77303d2e197',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $dataApiV1;

        $dataApiV2 = [
            '$02$e19c8a62fba81ef6baf2731e2ab0512feaf573ca5ca5929c2ee9a77303d2e197',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $dataApiV2;

        $dataApiPostV1 = [
            '$02$9d1971fb9ac51482f7e73dcf87fc029d4a3dfffa05314f71af9d89fb3c2bcf16',
            new Init($service, $security, $secret, $request, 'post', $this->signatureFactory)
        ];
        $testCases[] = $dataApiPostV1;

        $dataApiPostV2 = [
            '$02$9d1971fb9ac51482f7e73dcf87fc029d4a3dfffa05314f71af9d89fb3c2bcf16',
            new Init($service, $security, $secret, $request, 'post', $this->signatureFactory, '02')
        ];
        $testCases[] = $dataApiPostV2;

        $dataApiExpireV1 = [
            '$02$579bbf967c9fa886865fc85313bf0f70bdf3636a78732439ea19d6c2b908f49c',
            new Init($service, $securityExpires, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $dataApiExpireV1;
        $dataApiExpireV2 = [
            '$02$579bbf967c9fa886865fc85313bf0f70bdf3636a78732439ea19d6c2b908f49c',
            new Init($service, $securityExpires, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $dataApiExpireV2;

        /* Events */
        list($service, $security, $secret, $request, $action) = static::getWorkingEventsApiParams();
        $eventsApiV1 = [
            '$02$5c3160dbb9ab4d01774b5c2fc3b01a35ce4f9709c84571c27dfe333d1ca9d349',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $eventsApiV1;

        $eventsApiV2 = [
            '$02$5c3160dbb9ab4d01774b5c2fc3b01a35ce4f9709c84571c27dfe333d1ca9d349',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $eventsApiV2;

        /* Items */
        list($service, $security, $secret, $request, $action) = static::getWorkingItemsApiParams();
        $itemsApiV1 = [
            '$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $itemsApiV1;

        $itemsApiV2 = [
            '$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $itemsApiV2;

        /* Questions */
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();
        $questionsApiV1 = [
            '$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $questionsApiV1;

        $questionsApiV2 = [
            '$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $questionsApiV2;

        /* Reports */
        list($service, $security, $secret, $request, $action) = static::getWorkingReportsApiParams();
        $reportsApiV1 = [
            '$02$8e0069e7aa8058b47509f35be236c53fa1a878c64b12589fd42f48b568f6ac84',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory)
        ];
        $testCases[] = $reportsApiV1;

        $reportsApiV2 = [
            '$02$8e0069e7aa8058b47509f35be236c53fa1a878c64b12589fd42f48b568f6ac84',
            new Init($service, $security, $secret, $request, $action, $this->signatureFactory, '02')
        ];
        $testCases[] = $reportsApiV2;

        Init::enableTelemetry();

        return $testCases;
    }

    public function generateWithMetaProvider(): array
    {
        Init::enableTelemetry();

        $testCases = [];

        /* Author */
        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorApiParams();
        $authorApi = [
            'request.meta',
            (new Init($service, $security, $secret, $request, $action, $this->signatureFactory))->generate()
        ];
        $testCases[] = $authorApi;

        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorAideApiParams();
        $authorAideApi = [
            'request.meta',
            (new Init($service, $security, $secret, $request, $action, $this->signatureFactory))->generate()
        ];
        $testCases[] = $authorAideApi;

        /* Assess */
        list($service, $security, $secret, $request, $action) = static::getWorkingAssessApiParams();
        $assessApi = [
            'meta',
            (new Init($service, $security, $secret, $request, $action, $this->signatureFactory))->generate()
        ];
        $testCases[] = $assessApi;

        /* Data */
        list($service, $security, $secret, $request, $action) = static::getWorkingDataApiParams();
        $dataApi = [
            'request.meta',
            (new Init($service, $security, $secret, $request, $action, $this->signatureFactory))->generate()
        ];
        $testCases[] = $dataApi;

        /* Events */
        list($service, $security, $secret, $request, $action) = static::getWorkingEventsApiParams();
        $eventsApi = [
            'config.meta',
            (new Init($service, $security, $secret, $request, $action, $this->signatureFactory))->generate()
        ];
        $testCases[] = $eventsApi;

        /* Items */
        list($service, $security, $secret, $request, $action) = static::getWorkingItemsApiParams();
        $itemsApi = [
            'request.meta',
            (new Init($service, $security, $secret, $request, $action, $this->signatureFactory))->generate()
        ];
        $testCases[] = $itemsApi;

        /* Questions */
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();
        $questionsApi = [
            'meta',
            (new Init($service, $security, $secret, $request, $action, $this->signatureFactory))->generate()
        ];
        $testCases[] = $questionsApi;

        /* Reports */
        list($service, $security, $secret, $request, $action) = static::getWorkingReportsApiParams();
        $reportsApi = [
            'request.meta',
            (new Init($service, $security, $secret, $request, $action, $this->signatureFactory))->generate()
        ];
        $testCases[] = $reportsApi;

        Init::disableTelemetry();

        return $testCases;
    }
}
