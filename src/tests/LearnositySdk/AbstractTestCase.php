<?php

namespace LearnositySdk;

use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
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
}
