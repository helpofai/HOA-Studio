<?php

namespace App\Core\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AiException extends Exception
{
    protected string $errorCode;

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null, string $errorCode = 'AI_ERROR')
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => true,
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
        ], $this->getHttpStatusCode());
    }

    public function getHttpStatusCode(): int
    {
        return 500;
    }
}