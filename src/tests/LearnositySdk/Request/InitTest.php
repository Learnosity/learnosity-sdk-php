<?php

namespace tests\LearnositySdk\Request;

use LearnositySdk\Request\Init;

class InitTest extends \PHPUnit_Framework_TestCase
{
    const SECRET = '74c5fd430cf1242a527f6223aebd42d30464be22';

    /** @return array $security */
    public static function getSecurity() {
        return [
	    'consumer_key' => 'yis0TYCu7U9V4o7M',
	    'domain'       => 'localhost',
	    'timestamp'    => '20140626-0528',
        ];
    }

    /*
     * Tests 
     */

    /**
     * @param  array  $params
     * @param  string $expectedException
     * @param  string $expectedExceptionMessage
     *
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
            $this->setExpectedException($expectedException, $expectedExceptionMessage);
        }

        $init = new Init($service, $securityPacket, $secret, $requestPacket, $action);

        $this->assertEquals($expectedResult, $init);
    }

    /**
     * @param  string $expectedResult
     * @param  Init   $initObject
     *
     * @dataProvider generateSignatureProvider
     */
    public function testGenerateSignature($expectedResult, $initObject)
    {
        $this->assertEquals($expectedResult, $initObject->generateSignature());
    }

    /**
     * @param  string $expectedResult
     * @param  Init   $initObject
     *
     * @dataProvider generateProvider
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


    /*
     * Pseudo fixtures for parameters
     */

    /**
     * @param  boolean $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingAssessApiParams($assoc = false)
    {
        $service = 'assess';
        $security = static::getSecurity();
        // Needed to initialise Questions API
        $security['user_id'] = 'demo_student';
        $secret = static::SECRET;
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
                "user_id" => "demo_student",
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

    /**
     * @param  boolean $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingAuthorApiParams($assoc = false)
    {
        $service = 'author';
        $security = static::getSecurity();
        $secret = static::SECRET;
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

    /**
     * WARNING: RemoteTest is also using this params
     *
     * @param  boolean $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingDataApiParams($assoc = false)
    {
        $service = 'data';
        $security = static::getSecurity();
        $secret = static::SECRET;
        $request = array(
            'limit' => 100
        );
        $action = 'get';

        if ($assoc) {
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

    /**
     * @param  boolean $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingEventsApiParams($assoc = false)
    {
        $service = 'events';
        $security = static::getSecurity();
        $secret = static::SECRET;
        $request = [
            'users' => [
                'brianmoser' => '',
                'hankshrader' => '',
                'jessepinkman' => '',
                'walterwhite' => ''
            ]
        ];
        $action = null;

        if ($assoc) {
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

    /**
     * @param  boolean $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingItemsApiParams($assoc = false)
    {
        $service = 'items';
        $security = static::getSecurity();
        $secret = static::SECRET;
        $request = [
            'limit' => 50
        ];
        $action = null;

        if ($assoc) {
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

    /**
     * @param  boolean $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingQuestionsApiParams($assoc = false)
    {
        $service = 'questions';
        $security = static::getSecurity();
        $security['user_id'] = 'demo_student';
        $secret = static::SECRET;
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

    /**
     * @param  boolean $assoc If true, associative array will be returned
     * @return array
     */
    public static function getWorkingReportsApiParams($assoc = false)
    {
        $service = 'reports';
        $security = static::getSecurity();
        $secret = static::SECRET;
        $request = [
           "reports" => [
               [
                   "id" => "report-1",
                   "type" => "sessions-summary",
                   "user_id" => "brianmoser",
                   "session_ids" => [
                       "AC023456-2C73-44DC-82DA28894FCBC3BF"
                   ]
               ]
           ]
        ];
        $action = null;

        if ($assoc) {
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

    /*
     * Data providers
     */

    public function constructorProvider()
    {
        list($service, $security, $secret, $request, $action) = static::getWorkingDataApiParams();

        $wrongSecurity = $security;
        $wrongSecurity['wrongParam'] = '';

        return [
            [$service, $security, $secret, $request, $action, new Init($service, $security, $secret, $request, $action)],
            ['', $security, $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The `service` argument wasn\'t found or was empty'],
            ['wrongService', $security, $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The service provided (wrongService) is not valid'],
            [$service, '', $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The security packet must be an array'],
            [$service, $wrongSecurity, $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'Invalid key found in the security packet: wrongParam'],
            ['questions', $security, $secret, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'Questions API requires a `user_id` in the security packet'],
            [$service, $security, 25, $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The `secret` argument must be a valid string'],
            [$service, $security, '', $request, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The `secret` argument must be a valid string'],
            [$service, $security, $secret, 25, $action, null, '\LearnositySdk\Exceptions\ValidationException', 'The request packet must be an array'],
            [$service, $security, $secret, $request, 25, null, '\LearnositySdk\Exceptions\ValidationException', 'The `action` argument must be a string']
        ];
    }

    public function generateProvider()
    {
        $testCases = [];

        /* Author */
        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorApiParams();
        $authorApi = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"108b985a4db36ef03905572943a514fc02ed7cc6b700926183df7babc2cd1c96"},"request":{"mode":"item_list","config":{"item_list":{"item":{"status":true}}},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}}',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $authorApi;

        /* Assess */
        list($service, $security, $secret, $request, $action) = static::getWorkingAssessApiParams();
        $assessApi = [
            '{"items":[{"content":"<span class=\"learnosity-response question-demoscience1234\"></span>","response_ids":["demoscience1234"],"workflow":"","reference":"question-demoscience1"},{"content":"<span class=\"learnosity-response question-demoscience5678\"></span>","response_ids":["demoscience5678"],"workflow":"","reference":"question-demoscience2"}],"ui_style":"horizontal","name":"Demo (2 questions)","state":"initial","metadata":[],"navigation":{"show_next":true,"toc":true,"show_submit":true,"show_save":false,"show_prev":true,"show_title":true,"show_intro":true},"time":{"max_time":600,"limit_type":"soft","show_pause":true,"warning_time":60,"show_time":true},"configuration":{"onsubmit_redirect_url":"/assessment/","onsave_redirect_url":"/assessment/","idle_timeout":true,"questionsApiVersion":"v2"},"questionsApiActivity":{"user_id":"demo_student","type":"submit_practice","state":"initial","id":"assessdemo","name":"Assess API - Demo","questions":[{"response_id":"demoscience1234","type":"sortlist","description":"In this question, the student needs to sort the events, chronologically earliest to latest.","list":["Russian Revolution","Discovery of the Americas","Storming of the Bastille","Battle of Plataea","Founding of Rome","First Crusade"],"instant_feedback":true,"feedback_attempts":2,"validation":{"valid_response":[4,3,5,1,2,0],"valid_score":1,"partial_scoring":true,"penalty_score":-1}},{"response_id":"demoscience5678","type":"highlight","description":"The student needs to mark one of the flowers anthers in the image.","img_src":"http://www.learnosity.com/static/img/flower.jpg","line_color":"rgb(255, 20, 0)","line_width":"4"}],"consumer_key":"yis0TYCu7U9V4o7M","timestamp":"20140626-0528","signature":"0969eed4ca4bf483096393d13ee1bae35b993e5204ab0f90cc80eaa055605295"},"type":"activity"}',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $assessApi;

        /* Data */
        list($service, $security, $secret, $request, $action) = static::getWorkingDataApiParams();
        $security['timestamp'] = '20140626-0528';
        $dataApiGet = [
            [
                'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"e1eae0b86148df69173cb3b824275ea73c9c93967f7d17d6957fcdd299c8a4fe"}',
                'request'  => '{"limit":100}',
                'action'   => 'get'
            ],
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $dataApiGet;

        $dataApiPost = [
            [
                'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"18e5416041a13f95681f747222ca7bdaaebde057f4f222083881cd0ad6282c38"}',
                'request'  => '{"limit":100}',
                'action'   => 'post'
            ],
            new Init($service, $security, $secret, $request, 'post')
        ];
        $testCases[] = $dataApiPost;

        /* Events */
        list($service, $security, $secret, $request, $action) = static::getWorkingEventsApiParams();
        $eventsApiExpected = '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"20739eed410d54a135e8cb3745628834886ab315bfc01693ce9acc0d14dc98bf"},"config":{"users":{"brianmoser":"7224f1cd26c7eaac4f30c16ccf8e143005734089724affe0dd9cbf008b941e2d","hankshrader":"3f3edf8ad1f7d64186089308c34d0aee9d09324d1006df6dd3ce57ddc42c7f47","jessepinkman":"ca2d79d6e1c6c926f2b49f3d6052c060bed6b45e42786ff6c5293b9f3c723bdf","walterwhite":"fd1888ffc8cf87efb4ab620401130c76fc8dff5ca04f139e23a7437c56f8f310"}}}';

        $eventsApi = [
            $eventsApiExpected,
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $eventsApi;

        $request['users'] = array_keys($request['users']);
        $eventsApiUsersArray = [
            $eventsApiExpected,
            new Init($service, $security, $secret, $request, $action)
        ];
        // This case will trigger our warning; we can't really test for it, but we check that we are backward-compatible
        $testCases[] = $eventsApiUsersArray;

        /* Items */
        list($service, $security, $secret, $request, $action) = static::getWorkingItemsApiParams();
        $itemsApi = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"d61a62083712f8136e92b40a2c5ea340c77c81a30482da6c19b9c27e72d1f5eb"},"request":{"limit":50}}',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $itemsApi;

        /* Questions */
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();
        $questionsApi = [
            '{"consumer_key":"yis0TYCu7U9V4o7M","timestamp":"20140626-0528","user_id":"demo_student","signature":"0969eed4ca4bf483096393d13ee1bae35b993e5204ab0f90cc80eaa055605295","type":"local_practice","state":"initial","questions":[{"response_id":"60005","type":"association","stimulus":"Match the cities to the parent nation.","stimulus_list":["London","Dublin","Paris","Sydney"],"possible_responses":["Australia","France","Ireland","England"],"validation":{"valid_responses":[["England"],["Ireland"],["France"],["Australia"]]}}]}',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $questionsApi;

        /* Reports */
        list($service, $security, $secret, $request, $action) = static::getWorkingReportsApiParams();
        $reportsApi = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"217d82b0eb98b53e49f9367bed5a8c29d61e661946341c83cb2fcdbead78a8b2"},"request":{"reports":[{"id":"report-1","type":"sessions-summary","user_id":"brianmoser","session_ids":["AC023456-2C73-44DC-82DA28894FCBC3BF"]}]}}',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $reportsApi;

        return $testCases;
    }

    public function generateSignatureProvider()
    {
        $testCases = [];

        /* Author */
        list($service, $security, $secret, $request, $action) = static::getWorkingAuthorApiParams();
        $authorApi = [
            '108b985a4db36ef03905572943a514fc02ed7cc6b700926183df7babc2cd1c96',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $authorApi;

        /* Assess */
        list($service, $security, $secret, $request, $action) = static::getWorkingAssessApiParams();
        $assessApi = [
            '0969eed4ca4bf483096393d13ee1bae35b993e5204ab0f90cc80eaa055605295',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $assessApi;

        /* Data */
        list($service, $security, $secret, $request, $action) = static::getWorkingDataApiParams();
        $securityExpires = $security;
        $securityExpires['expires'] = '20160621-1716';

        $dataApi = [
            'e1eae0b86148df69173cb3b824275ea73c9c93967f7d17d6957fcdd299c8a4fe',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $dataApi;

        $dataApiPost = [
            '18e5416041a13f95681f747222ca7bdaaebde057f4f222083881cd0ad6282c38',
            new Init($service, $security, $secret, $request, 'post')
        ];
        $testCases[] = $dataApiPost;

        $dataApiExpire= [
            '5d962d5fea8e5413bddc0f304650c4b58ed4419015e47934452127dc2120fd8a',
            new Init($service, $securityExpires, $secret, $request, $action)
        ];
        $testCases[] = $dataApiExpire;

        /* Events */
        list($service, $security, $secret, $request, $action) = static::getWorkingEventsApiParams();
        $eventsApi = [
            '20739eed410d54a135e8cb3745628834886ab315bfc01693ce9acc0d14dc98bf',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $eventsApi;

        /* Items */
        list($service, $security, $secret, $request, $action) = static::getWorkingItemsApiParams();
        $itemsApi = [
            'd61a62083712f8136e92b40a2c5ea340c77c81a30482da6c19b9c27e72d1f5eb',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $itemsApi;

        /* Questions */
        list($service, $security, $secret, $request, $action) = static::getWorkingQuestionsApiParams();
        $questionsApi = [
            '0969eed4ca4bf483096393d13ee1bae35b993e5204ab0f90cc80eaa055605295',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $questionsApi;

        /* Reports */
        list($service, $security, $secret, $request, $action) = static::getWorkingReportsApiParams();
        $reportsApi = [
            '217d82b0eb98b53e49f9367bed5a8c29d61e661946341c83cb2fcdbead78a8b2',
            new Init($service, $security, $secret, $request, $action)
        ];
        $testCases[] = $reportsApi;

        return $testCases;
    }
}
