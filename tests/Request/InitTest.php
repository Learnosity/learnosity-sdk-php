<?php

namespace LearnositySdk\Request;

use LearnositySdk\AbstractTestCase; // XXX: should be in a Test namespace
use LearnositySdk\Exceptions\ValidationException;
use LearnositySdk\Fixtures\ParamsFixture; // XXX: should be in a Test namespace
use LearnositySdk\Services\SignatureFactory;

class InitTest extends AbstractTestCase
{
    /**
     * @var \LearnositySdk\Services\SignatureFactory
     */
    private $signatureFactory;

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
        // We disable telemetry to be able to reliably test signature generation. Added telemetry
        // will differ on each platform tests would be run, and therefore fail.
        Init::disableTelemetry();


        $init = new Init($service, $securityPacket, $secret, $requestPacket, $action);

        $this->assertEquals($expectedResult, $init);
    }

    /**
     * @dataProvider generateWithMetaProvider
     */
    public function testGenerateWithMeta(
        string $pathToMeta,
        string $service,
        array $security,
        string $secret,
        array|string $request,
        ?string $action
    ) {
        // This test verifies the correctness of the added telemetry data
        Init::enableTelemetry();
        $initObject = new Init($service, $security, $secret, $request, $action);
        $generated = $initObject->generate();

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
    public function testGenerateSignature(
        string $expectedSignature,
        string $service,
        array $security,
        string $secret,
        array|string $request,
        ?string $action
    ) {
        // We disable telemetry to be able to reliably test signature generation. Added telemetry
        // will differ on each platform tests would be run, and therefore fail.
        Init::disableTelemetry();
        $initObject = new Init($service, $security, $secret, $request, $action);

        $this->assertEquals($expectedSignature, $initObject->generateSignature());
    }

    /**
     * @dataProvider generateProvider
     */
    public function testGenerate(
        array|string $expectedInitOptions,
        string $service,
        array $security,
        string $secret,
        array|string $request,
        ?string $action
    ) {
        // We disable telemetry to be able to reliably test signature generation. Added telemetry
        // will differ on each platform tests would be run, and therefore fail.
        Init::disableTelemetry();

        $initObject = new Init($service, $security, $secret, $request, $action);
        $generated = $initObject->generate();

        if (is_array($expectedInitOptions)) {
            ksort($expectedInitOptions);
        }

        if (is_array($generated)) {
            ksort($generated);
        }

        $this->assertEquals($expectedInitOptions, $generated);
    }

    public function testNullRequestPacketGeneratesValidInit()
    {
        list($service, $security, $secret) = ParamsFixture::getWorkingDataApiParams();
        $initObject = new Init($service, $security, $secret, null, null);
        $this->assertInstanceOf(Init::class, $initObject);
    }

    public function testEmptyArrayRequestPacketGeneratesValidInit()
    {
        list($service, $security, $secret) = ParamsFixture::getWorkingDataApiParams();
        $initObject = new Init($service, $security, $secret, [], null);
        $this->assertInstanceOf(Init::class, $initObject);
    }

    public function testEmptyStringGeneratesValidInit()
    {
        list($service, $security, $secret) = ParamsFixture::getWorkingDataApiParams();
        $initObject = new Init($service, $security, $secret, "", null);
        $this->assertInstanceOf(Init::class, $initObject);
    }

    public function testNullRequestPacketAndActionGeneratesValidInit()
    {
        list($service, $security, $secret) = ParamsFixture::getWorkingDataApiParams();
        $initObject = new Init($service, $security, $secret, null, null);
        $this->assertInstanceOf(Init::class, $initObject);
    }

    public function testMetaWithTelemetryOnlyAddsSdkProp()
    {
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingQuestionsApiParams();

        // This test verifies the correctness of the added telemetry data
        Init::enableTelemetry();

        $initObject = new Init($service, $security, $secret, json_encode($request), $action);
        $generatedObject = json_decode($initObject->generate());

        // when telemetry is enabled, if the $request has no meta field,
        // then the meta of the generated object has the sdk field only
        $this->assertObjectHasProperty('meta', $generatedObject);
        $this->assertObjectHasProperty('sdk', $generatedObject->meta);
        $this->assertEquals(1, count((array) $generatedObject->meta));
    }

    public function testRequestWithTelemetryPreservesOtherMetaProps()
    {
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingQuestionsApiParams();

        // add meta field to the $request
        $request['meta'] = ParamsFixture::getMetaField();

        // generate a new $initObject using the updated $request
        $initObject = new Init($service, $security, $secret, json_encode($request), $action);
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
        // We disable telemetry to be able to reliably test signature generation. Added telemetry
        // will differ on each platform tests would be run, and therefore fail.
        Init::disableTelemetry();
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingQuestionsApiParams();

        $initObject = new Init($service, $security, $secret, json_encode($request), $action);
        $generatedObject = json_decode($initObject->generate());

        // when telemetry is disabled, if the meta field of the $request is empty,
        // then the meta of the generated object should also be empty
        $this->assertObjectNotHasProperty('meta', $generatedObject);
    }

    public function testRequestWithoutTelemetryPreservesFilledMeta()
    {
        // We disable telemetry to be able to reliably test signature generation. Added telemetry
        // will differ on each platform tests would be run, and therefore fail.
        Init::disableTelemetry();
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingQuestionsApiParams();

        // add meta field to the $request
        $request['meta'] = ParamsFixture::getMetaField();

        $initObject = new Init($service, $security, $secret, json_encode($request), $action);
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
     * Data providers
     */

    public function constructorProvider(): array
    {
        // We disable telemetry to be able to reliably test signature generation. Added telemetry
        // will differ on each platform tests would be run, and therefore fail.
        Init::disableTelemetry();

        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingDataApiParams();

        $wrongSecurity = $security;
        $wrongSecurity['wrongParam'] = '';

        return [
            'valid-api-data' => [
                $service,
                $security,
                $secret,
                $request,
                $action,
                new Init($service, $security, $secret, $request, $action)
            ],
            'empty-service' => [
                '',
                $security,
                $secret,
                $request,
                $action,
                null,
                ValidationException::class,
                'The `service` argument wasn\'t found or was empty'
            ],
            'invalid-service' => [
                'wrongService',
                $security,
                $secret,
                $request,
                $action,
                null,
                ValidationException::class,
                'The service provided (wrongService) is not valid'
            ],
            'empty-security' => [

                $service,
                '',
                $secret,
                $request,
                $action,
                null,
                ValidationException::class,
                'The security packet must be an array or a valid JSON string'
            ],
            'empty-security' => [

                $service,
                '',
                $secret,
                $request,
                $action,
                null,
                ValidationException::class,
                'The security packet must be an array or a valid JSON string'
            ],
            'null-security' => [

                $service,
                null,
                $secret,
                $request,
                $action,
                null,
                ValidationException::class,
                'The security packet must be an array or a valid JSON string'
            ],
            'empty-secret' => [

                $service,
                $security,
                '',
                $request,
                $action,
                null,
                ValidationException::class,
                'The `secret` argument must be a valid string'
            ],
            'incorrect-security' => [

                $service,
                $wrongSecurity,
                $secret,
                $request,
                $action,
                null,
                ValidationException::class,
                'Invalid key found in the security packet: wrongParam'
            ],
            'missing-questions_user_id' =>
            [

                'questions',
                $security,
                $secret,
                $request,
                $action,
                null,
                ValidationException::class,
                'Questions API requires a `user_id` in the security packet'
            ],
            'invalid-request' =>
            [

                $service,
                $security,
                $secret,
                25,
                $action,
                null,
                ValidationException::class,
                'The request packet must be an array or a valid JSON string'
            ],
        ];
    }

    /** @return array:
     *  - string $expectedSignedInitOptions
     *  - string $service
     *  - array $security
     *  - string $secret
     *  - array|string $request
     *  - ?string $action
     */
    public function generateProvider(): array
    {
        $testCases = [];

        /* Author */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAuthorApiParams();
        $authorApi = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861"},"request":{"mode":"item_list","config":{"item_list":{"item":{"status":true}}},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}}',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-author'] = $authorApi;

        /* Author Aide */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAuthorAideApiParams();
        $authorAideApi = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$f2ce1da2fdead193d53ab954b8a3660548ed9b0e3ce60599d751130deba7a138"},"request":{"config":{"test-attribute":"test"},"user":{"id":"walterwhite","firstname":"walter","lastname":"white"}}}',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-authoraide'] = $authorAideApi;

        /* Assess */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAssessApiParams();
        $assessApi = [
            '{"items":[{"content":"<span class=\"learnosity-response question-demoscience1234\"></span>","response_ids":["demoscience1234"],"workflow":"","reference":"question-demoscience1"},{"content":"<span class=\"learnosity-response question-demoscience5678\"></span>","response_ids":["demoscience5678"],"workflow":"","reference":"question-demoscience2"}],"ui_style":"horizontal","name":"Demo (2 questions)","state":"initial","metadata":[],"navigation":{"show_next":true,"toc":true,"show_submit":true,"show_save":false,"show_prev":true,"show_title":true,"show_intro":true},"time":{"max_time":600,"limit_type":"soft","show_pause":true,"warning_time":60,"show_time":true},"configuration":{"onsubmit_redirect_url":"/assessment/","onsave_redirect_url":"/assessment/","idle_timeout":true,"questionsApiVersion":"v2"},"questionsApiActivity":{"type":"local_practice","state":"initial","questions":[{"response_id":"60005","type":"association","stimulus":"Match the cities to the parent nation.","stimulus_list":["London","Dublin","Paris","Sydney"],"possible_responses":["Australia","France","Ireland","England"],"validation":{"valid_responses":[["England"],["Ireland"],["France"],["Australia"]]}}],"user_id":"$ANONYMIZED_USER_ID","name":"Assess API - Demo","id":"assessdemo","consumer_key":"yis0TYCu7U9V4o7M","timestamp":"20140626-0528","signature":"$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8"},"type":"activity"}',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-assess'] = $assessApi;

        /* Data */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingDataApiParams();
        $dataApiGet = [
            [
                'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$e19c8a62fba81ef6baf2731e2ab0512feaf573ca5ca5929c2ee9a77303d2e197"}',
                'request'  => '{"limit":100}',
                'action'   => 'get'
            ],
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-data_get'] = $dataApiGet;

        $dataApiPost = [
            [
                'security' => '{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$9d1971fb9ac51482f7e73dcf87fc029d4a3dfffa05314f71af9d89fb3c2bcf16"}',
                'request'  => '{"limit":100}',
                'action'   => 'post'
            ],
            $service, $security, $secret, $request, 'post',
        ];
        // XXX: post is not a valid action; should be set
        $testCases['api-data_post'] = $dataApiPost;

        /* Events */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingEventsApiParams();
        $eventsApiExpected = '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$5c3160dbb9ab4d01774b5c2fc3b01a35ce4f9709c84571c27dfe333d1ca9d349"},"config":{"users":{"$ANONYMIZED_USER_ID_1":"64ccf06154cf4133624372459ebcccb8b2f8bd7458a73df681acef4e742e175c","$ANONYMIZED_USER_ID_2":"7fa4d6ef8926add8b6411123fce916367250a6a99f50ab8ec39c99d768377adb","$ANONYMIZED_USER_ID_3":"3d5b26843da9192319036b67f8c5cc26e1e1763811270ba164665d0027296952","$ANONYMIZED_USER_ID_4":"3b6ac78f60f3e3eb7a85cec8b48bdca0f590f959e0a87a9c4222898678bd50c8"}}}';
        $eventsApi = [
            $eventsApiExpected,
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-events'] = $eventsApi;

        /* Items */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingItemsApiParams();
        $itemsApi = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9"},"request":{"user_id":"$ANONYMIZED_USER_ID","rendering_type":"assess","name":"Items API demo - assess activity demo","state":"initial","activity_id":"items_assess_demo","session_id":"demo_session_uuid","type":"submit_practice","config":{"configuration":{"responsive_regions":true},"navigation":{"scrolling_indicator":true},"regions":"main","time":{"show_pause":true,"max_time":300},"title":"ItemsAPI Assess Isolation Demo","subtitle":"Testing Subtitle Text"},"items":["Demo3"]}}',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-items'] = $itemsApi;

        /* Questions */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingQuestionsApiParams();
        $questionsApi = [
            '{"consumer_key":"yis0TYCu7U9V4o7M","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8","type":"local_practice","state":"initial","questions":[{"response_id":"60005","type":"association","stimulus":"Match the cities to the parent nation.","stimulus_list":["London","Dublin","Paris","Sydney"],"possible_responses":["Australia","France","Ireland","England"],"validation":{"valid_responses":[["England"],["Ireland"],["France"],["Australia"]]}}]}',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-questions'] = $questionsApi;

        /* Reports */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingReportsApiParams();
        $reportsApi = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$8e0069e7aa8058b47509f35be236c53fa1a878c64b12589fd42f48b568f6ac84"},"request":{"reports":[{"id":"report-1","type":"sessions-summary","user_id":"$ANONYMIZED_USER_ID","session_ids":["AC023456-2C73-44DC-82DA28894FCBC3BF"]}]}}',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-reports'] = $reportsApi;

        /* Passing request as string */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAuthorApiParams();
        $authorApiAsString = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861"},"request":"{\"mode\":\"item_list\",\"config\":{\"item_list\":{\"item\":{\"status\":true}}},\"user\":{\"id\":\"walterwhite\",\"firstname\":\"walter\",\"lastname\":\"white\"}}"}',
            $service, $security, $secret, json_encode($request), $action,
        ];
        $testCases['api-author_string'] = $authorApiAsString;

        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAuthorAideApiParams();
        $authorAideApiAsString = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","signature":"$02$f2ce1da2fdead193d53ab954b8a3660548ed9b0e3ce60599d751130deba7a138"},"request":"{\"config\":{\"test-attribute\":\"test\"},\"user\":{\"id\":\"walterwhite\",\"firstname\":\"walter\",\"lastname\":\"white\"}}"}',
            $service, $security, $secret, json_encode($request), $action,
        ];
        $testCases['api-authoraide_string'] = $authorAideApiAsString;

        /* Items */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingItemsApiParams();
        $itemsApiAsString = [
            '{"security":{"consumer_key":"yis0TYCu7U9V4o7M","domain":"localhost","timestamp":"20140626-0528","user_id":"$ANONYMIZED_USER_ID","signature":"$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9"},"request":"{\"user_id\":\"$ANONYMIZED_USER_ID\",\"rendering_type\":\"assess\",\"name\":\"Items API demo - assess activity demo\",\"state\":\"initial\",\"activity_id\":\"items_assess_demo\",\"session_id\":\"demo_session_uuid\",\"type\":\"submit_practice\",\"config\":{\"configuration\":{\"responsive_regions\":true},\"navigation\":{\"scrolling_indicator\":true},\"regions\":\"main\",\"time\":{\"show_pause\":true,\"max_time\":300},\"title\":\"ItemsAPI Assess Isolation Demo\",\"subtitle\":\"Testing Subtitle Text\"},\"items\":[\"Demo3\"]}"}',
            $service, $security, $secret, json_encode($request), $action,
        ];
        $testCases['api-items_string'] = $itemsApiAsString;

        Init::enableTelemetry();

        return $testCases;
    }

    /** @return array:
     *  - string $expectedSignature
     *  - string $service
     *  - array $security
     *  - string $secret
     *  - array|string $request
     *  - ?string $action
     */
    public function generateSignatureProvider(): array
    {
        $testCases = [];

        /* Author */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAuthorApiParams();
        $authorApi = [
            '$02$ca2769c4be77037cf22e0f7a2291fe48c470ac6db2f45520a259907370eff861',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-author'] = $authorApi;

        /* Assess */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAssessApiParams();
        $assessApi = [
            '$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-assess'] = $assessApi;

        /* Data */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingDataApiParams();
        $securityExpires = $security;
        $securityExpires['expires'] = '20160621-1716';

        $dataApi = [
            '$02$e19c8a62fba81ef6baf2731e2ab0512feaf573ca5ca5929c2ee9a77303d2e197',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-data'] = $dataApi;

        $dataApiPost = [
            '$02$9d1971fb9ac51482f7e73dcf87fc029d4a3dfffa05314f71af9d89fb3c2bcf16',
            $service, $security, $secret, $request, 'post',
        ];
        // XXX: post is not a valid action; should be set
        $testCases['api-data_post'] = $dataApiPost;

        $dataApiExpire = [
            '$02$579bbf967c9fa886865fc85313bf0f70bdf3636a78732439ea19d6c2b908f49c',
            $service, $securityExpires, $secret, $request, $action,
        ];
        $testCases['api-data_expire'] = $dataApiExpire;

        /* Events */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingEventsApiParams();
        $eventsApi = [
            '$02$5c3160dbb9ab4d01774b5c2fc3b01a35ce4f9709c84571c27dfe333d1ca9d349',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-events'] = $eventsApi;

        /* Items */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingItemsApiParams();
        $itemsApi = [
            '$02$36c439e7d18f2347ce08ca4b8d4803a22325d54352650b19b6f4aaa521b613d9',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-items'] = $itemsApi;

        /* Questions */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingQuestionsApiParams();
        $questionsApi = [
            '$02$8de51b7601f606a7f32665541026580d09616028dde9a929ce81cf2e88f56eb8',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-questions'] = $questionsApi;

        /* Reports */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingReportsApiParams();
        $reportsApi = [
            '$02$8e0069e7aa8058b47509f35be236c53fa1a878c64b12589fd42f48b568f6ac84',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-reports'] = $reportsApi;

        return $testCases;
    }

    /** @return array:
     *  - string $pathToMeta
     *  - string $service
     *  - array $security
     *  - string $secret
     *  - array|string $request
     *  - ?string $action
     */
    public function generateWithMetaProvider(): array
    {
        $testCases = [];

        /* Author */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAuthorApiParams();
        $authorApi = [
            'request.meta',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-author'] = $authorApi;

        /* Author Aide */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAuthorAideApiParams();
        $authorAideApi = [
            'request.meta',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-authoraide'] = $authorAideApi;

        /* Assess */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingAssessApiParams();
        $assessApi = [
            'meta',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-assess'] = $assessApi;

        /* Data */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingDataApiParams();
        $dataApi = [
            'request.meta',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-data'] = $dataApi;

        /* Events */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingEventsApiParams();
        $eventsApi = [
            'config.meta',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-events'] = $eventsApi;

        /* Items */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingItemsApiParams();
        $itemsApi = [
            'request.meta',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-items'] = $itemsApi;

        /* Questions */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingQuestionsApiParams();
        $questionsApi = [
            'meta',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-questions'] = $questionsApi;

        /* Reports */
        list($service, $security, $secret, $request, $action) = ParamsFixture::getWorkingReportsApiParams();
        $reportsApi = [
            'request.meta',
            $service, $security, $secret, $request, $action,
        ];
        $testCases['api-reports'] = $reportsApi;

        return $testCases;
    }
}
