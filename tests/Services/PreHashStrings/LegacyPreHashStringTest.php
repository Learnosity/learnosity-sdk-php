<?php

namespace LearnositySdk\Services\PreHashStrings;

use LearnositySdk\AbstractTestCase;
use LearnositySdk\Exceptions\ValidationException;
use LearnositySdk\Fixtures\ParamsFixture;

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
        $testCases = [];

        $requestAsString = true;

        /* Hardcoded prehash strings generated with the last version of the legacy code,
         * based on queries from the ParamsFixture, depending on v1Compat mode */
        $preHashStrings = [
            false => [
                'assess' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_$ANONYMIZED_USER_ID',
                'author' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_{"mode":"item_list","config":{"item_list":{"item":{"status":true}}},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}',
                'authoraide' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_{"config":{"test-attribute":"test"},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}',
                'data' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_{"limit":100}_get',
                'events' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528',
                'items' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_$ANONYMIZED_USER_ID_{"user_id":"$ANONYMIZED_USER_ID","rendering_type":"assess","name":"Items API demo - assess activity demo","state":"initial","activity_id":"items_assess_demo","session_id":"demo_session_uuid","type":"submit_practice","config":{"configuration":{"responsive_regions":true},"navigation":{"scrolling_indicator":true},"regions":"main","time":{"show_pause":true,"max_time":300},"title":"ItemsAPI Assess Isolation Demo","subtitle":"Testing Subtitle Text"},"items":["Demo3"]}',
                'questions' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_$ANONYMIZED_USER_ID',
                'reports' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_{"reports":[{"id":"report-1","type":"sessions-summary","user_id":"$ANONYMIZED_USER_ID","session_ids":["AC023456-2C73-44DC-82DA28894FCBC3BF"]}]}',
            ],
            true => [
                /* Generated from a the ParamsFixture knowing that a v1 signature was generated correctly */
                'assess' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_$ANONYMIZED_USER_ID_74c5fd430cf1242a527f6223aebd42d30464be22',
                'author' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_74c5fd430cf1242a527f6223aebd42d30464be22_{"mode":"item_list","config":{"item_list":{"item":{"status":true}}},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}',
                'authoraide' => null, /* no need for v1 compat, let's make this explicit */
                'data' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_74c5fd430cf1242a527f6223aebd42d30464be22_{"limit":100}_get',
                'events' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_74c5fd430cf1242a527f6223aebd42d30464be22',
                'items' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_$ANONYMIZED_USER_ID_74c5fd430cf1242a527f6223aebd42d30464be22_{"user_id":"$ANONYMIZED_USER_ID","rendering_type":"assess","name":"Items API demo - assess activity demo","state":"initial","activity_id":"items_assess_demo","session_id":"demo_session_uuid","type":"submit_practice","config":{"configuration":{"responsive_regions":true},"navigation":{"scrolling_indicator":true},"regions":"main","time":{"show_pause":true,"max_time":300},"title":"ItemsAPI Assess Isolation Demo","subtitle":"Testing Subtitle Text"},"items":["Demo3"]}',
                'questions' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_$ANONYMIZED_USER_ID_74c5fd430cf1242a527f6223aebd42d30464be22',
                'reports' => 'yis0TYCu7U9V4o7M_localhost_20140626-0528_74c5fd430cf1242a527f6223aebd42d30464be22_{"reports":[{"id":"report-1","type":"sessions-summary","user_id":"$ANONYMIZED_USER_ID","session_ids":["AC023456-2C73-44DC-82DA28894FCBC3BF"]}]}',
            ],
        ];

        foreach ([true, false] as $v1Compat) {
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

                $tc['expected'] = $preHashStrings[$v1Compat][$service];

                if (is_null($case['expected'])) {
                    /* New APIs don't need v1Compat support */
                    continue;
                }

                $testCases["api-{$service}" . ($v1Compat ? '-v1Compat' : '')] = $tc;
            }
        }

        return $testCases;
    }
}
