<?php

namespace App\Core\Exceptions;

class AiProviderDownException extends AiException
{
    public function __construct(
        string $message = 'AI provider is currently unavailable. Please try again later.',
        string $provider = 'unknown',
        int $retryAfter = 30,
        ?Exception $previous = null
    ) {
        parent::__construct($message, 503, $previous, 'PROVIDER_UNAVAILABLE');
        $this->provider = $provider;
        $this->retryAfter = $retryAfter;
    }

    public function getHttpStatusCode(): int
    {
        return 503;
    }
}