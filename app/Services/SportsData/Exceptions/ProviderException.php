<?php

namespace App\Services\SportsData\Exceptions;

use RuntimeException;

class ProviderException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('No football data provider is configured. Add an API key in Admin → Sports API settings or enable demo data.');
    }
}
