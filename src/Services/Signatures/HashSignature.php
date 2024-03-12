<?php

namespace LearnositySdk\Services\Signatures;

use LearnositySdk\Exceptions\ValidationException;

class HashSignature implements SignatureInterface
{
    public const ALGORITHM = 'sha256';

    public const SIGNATURE_VERSION = '01';

    public const CONSUMER_KEY_LENGTH = 16;

    public const TIMESTAMP_KEY_LENGTH = 13;

    public const SIGNATURE_KEY_LENGTH = 64;

    public const EXCEPTION_MESSAGE =
        'The pre hash string for this signature type must contain the secret key';

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
        if (!strpos($preHashString, $secretKey)) {
            throw new ValidationException(static::EXCEPTION_MESSAGE);
        }
        return hash(static::ALGORITHM, $preHashString);
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
