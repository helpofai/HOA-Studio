<?php

namespace App\Core\Exceptions;

class AiTokenLimitException extends AiException
{
    public function __construct(
        string $message = 'Token limit exceeded. The request is too large for the selected model.',
        int $requestedTokens = 0,
        int $maxTokens = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, 413, $previous, 'TOKEN_LIMIT_EXCEEDED');
        $this->requestedTokens = $requestedTokens;
        $this->maxTokens = $maxTokens;
    }

    public function getHttpStatusCode(): int
    {
        return 413;
    }
}