<?php

namespace App\Services\SportsData\Exceptions;

class RateLimitedException extends ProviderException
{
    public function __construct(string $message = 'Sports data provider rate limit reached.', public readonly int $retryAfter = 60)
    {
        parent::__construct($message);
    }
}
