<?php

namespace App\Integrations\Petstore;

use RuntimeException;

class PetstoreException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly string $responseBody = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public static function fromHttpError(int $statusCode, string $body): self
    {
        return new self(
            message: "Petstore API error (HTTP {$statusCode})",
            statusCode: $statusCode,
            responseBody: $body,
        );
    }

    public static function networkError(string $message, \Throwable $previous): self
    {
        return new self(
            message: "Petstore API network error: {$message}",
            statusCode: 0,
            responseBody: '',
            previous: $previous,
        );
    }

    public static function invalidResponse(string $details): self
    {
        return new self(
            message: "Petstore API returned an invalid response: {$details}",
            statusCode: 0,
            responseBody: $details,
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }
}

