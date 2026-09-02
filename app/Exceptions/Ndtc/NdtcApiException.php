<?php

namespace App\Exceptions\Ndtc;

class NdtcApiException extends \Exception
{
    public function __construct(
        public readonly int $httpStatusCode,
        public readonly ?string $errorCode,
        public readonly ?string $errorDescription,
        public readonly ?string $errorDetails,
        public readonly string $rawBody,
    ) {
        parent::__construct("NDTC API error {$httpStatusCode}: {$rawBody}");
    }

    public static function fromResponse(int $status, string $body): self
    {
        $decoded = json_decode($body, true);
        $firstError = $decoded['resultInfo']['errors'][0] ?? null;

        return new self(
            httpStatusCode: $status,
            errorCode: $firstError['code'] ?? null,
            errorDescription: $firstError['description'] ?? null,
            errorDetails: $firstError['details'] ?? null,
            rawBody: $body,
        );
    }

    public function isOrderNotFound(): bool
    {
        return $this->httpStatusCode === 404
            && $this->errorCode === '1002'
            && str_contains((string) $this->errorDetails, 'Order not found');
    }
}
