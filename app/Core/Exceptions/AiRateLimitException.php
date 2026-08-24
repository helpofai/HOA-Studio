<?php

namespace App\Core\Exceptions;

class AiRateLimitException extends AiException
{
    public function __construct(string $message = 'Rate limit exceeded. Please wait before making more requests.', int $retryAfter = 60, ?Exception $previous = null)
    {
        parent::__construct($message, 429, $previous, 'RATE_LIMIT_EXCEEDED');
        $this->retryAfter = $retryAfter;
    }

    public function getHttpStatusCode(): int
    {
        return 429;
    }
}