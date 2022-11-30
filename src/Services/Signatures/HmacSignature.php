<?php

namespace LearnositySdk\Services\Signatures;

use Exception;
use LearnosityPackages\Exceptions\AuthenticationException;
use LearnositySdk\Exceptions\ValidationException;

class HmacSignature implements SignatureInterface
{
    const ALGORITHM = 'sha256';

    const SIGNATURE_VERSION = '02';

    const CONSUMER_KEY_LENGTH = 16;

    const TIMESTAMP_KEY_LENGTH = 13;

    const SIGNATURE_KEY_LENGTH = 68;

    const EXCEPTION_MESSAGE =
        'The pre hash string for this signature type must not contain the secret key';

    /**
     * @param string $preHashString
     * @param string $secretKey
     * @return string
     * @throws ValidationException
     */
    public function sign(
        string $preHashString,
        string $secretKey
    ): string {
        if (strpos($preHashString, $secretKey)) {
            throw new ValidationException(static::EXCEPTION_MESSAGE);
        }
        return '$' . static::SIGNATURE_VERSION . '$' . hash_hmac(static::ALGORITHM,
            $preHashString,
            $secretKey
        );
    }

    /**
     * @param array $security
     * @return bool
     */
    public function validateParameterLengths(array $security): bool
    {
        return strlen($security['consumer_key']) !== static::CONSUMER_KEY_LENGTH
            || strlen($security['timestamp']) !== static::TIMESTAMP_KEY_LENGTH
            || strlen($security['signature']) !== static::SIGNATURE_KEY_LENGTH;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return static::SIGNATURE_VERSION;
    }

}
