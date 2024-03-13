<?php

namespace LearnositySdk\Fixtures;

// XXX: should be in a Test namespace

class ParamsFixture
{
    const TEST_CONSUMER_KEY = 'yis0TYCu7U9V4o7M';
    const TEST_CONSUMER_SECRET = '74c5fd430cf1242a527f6223aebd42d30464be22';
    const TEST_DOMAIN = 'localhost';

    public static function getSecurity(): array
    {
        return [
            'consumer_key' => static::TEST_CONSUMER_KEY,
            'domain'       => static::TEST_DOMAIN,
            'timestamp'    => '20140626-0528',
        ];
    }

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

        $questionsParams = static::getWorkingQuestionsApiParams(true);
        $questionsApiActivity = $questionsParams['request'];
        $questionsApiActivity['user_id'] = $security['user_id'];
        $questionsApiActivity['name'] = 'Assess API - Demo';
        $questionsApiActivity['id'] = 'assessdemo';

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
            "ui_style" => "horizontal",
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
            "questionsApiActivity" => $questionsApiActivity,
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
        $security['timestamp'] = '20140626-0528';
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
        $security['user_id'] = '$ANONYMIZED_USER_ID';
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
    public static function getMetaField(): array
    {
        return [
            "test_key_string" => "test-string",
            "test_key_integer" => 12345,
            "test_key_boolean" => true,
        ];
    }
}
