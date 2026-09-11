<?php

namespace App\Services\Ndtc;

use App\Exceptions\Ndtc\NdtcApiException;
use App\Models\NdtcApiCallLog;
use App\Models\NdtcOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NdtcApiService
{
    private NdtcAuthService $auth;
    private string $baseUrl;

    public function __construct(NdtcAuthService $auth)
    {
        $this->auth    = $auth;
        $this->baseUrl = config('ndtc.base_url');
    }

    // ── ORDER ENDPOINTS ───────────────────────────────────────────

    public function createOrder(array $payload, string $transactionType = 'TNL', ?string $correlationId = null): array
    {
        $endpoint = $this->getOrderEndpoint($transactionType);
        return $this->request('POST', $endpoint, $payload);
    }

    private function getOrderEndpoint(string $transactionType): string
    {
        return match($transactionType) {
            'TNL'   => '/api/v3/clearinghouse/orders/transfer-no-lien',
            'TWL'   => '/api/v3/clearinghouse/orders/transfer-with-lien',
            'TWEL'  => '/api/v3/clearinghouse/orders/transfer-with-electronic-lien',
            'DNT'   => '/api/v3/clearinghouse/orders/dealer-no-title',
            'EOL'   => '/api/v3/clearinghouse/orders/end-of-lease',
            'RT'    => '/api/v3/clearinghouse/orders/recovered-theft',
            'RWT'   => '/api/v3/clearinghouse/orders/repossession-with-title',
            'RWUT'  => '/api/v3/clearinghouse/orders/repossession-with-unfiled-title',
            'RWOT'  => '/api/v3/clearinghouse/orders/repossession-without-title',
            'SNL'   => '/api/v3/clearinghouse/orders/salvage-no-lien',
            'SNT'   => '/api/v3/clearinghouse/orders/salvage-no-title',
            'SWL'   => '/api/v3/clearinghouse/orders/salvage-with-lien',
            'SPR'   => '/api/v3/clearinghouse/orders/single-party-retitling',
            'SPS'   => '/api/v3/clearinghouse/orders/single-party-salvage',
            'UTNL'  => '/api/v3/clearinghouse/orders/unrecovered-theft-no-lien',
            'UTNT'  => '/api/v3/clearinghouse/orders/unrecovered-theft-no-title',
            'UTWL'  => '/api/v3/clearinghouse/orders/unrecovered-theft-with-lien',
            default => throw new \Exception("Unsupported transaction type: {$transactionType}"),
        };
    }

    public function updateOrder(string $ndtcOrderId, array $payload, ?NdtcOrder $order = null): array
    {
        return $this->request('PUT', "/api/v3/clearinghouse/orders/{$ndtcOrderId}", $payload, $order);
    }

    public function finalizeOrder(string $ndtcOrderId, ?NdtcOrder $order = null): array
    {
        return $this->request('POST', "/api/v3/clearinghouse/orders/{$ndtcOrderId}/finalize", [], $order);
    }

    public function cancelOrder(string $ndtcOrderId, ?NdtcOrder $order = null): void
    {
        $this->request('DELETE', "/api/v3/clearinghouse/orders/{$ndtcOrderId}", [], $order);
    }

    public function getOrder(string $ndtcOrderId, ?NdtcOrder $order = null): array
    {
        return $this->request('GET', "/api/v3/clearinghouse/orders/{$ndtcOrderId}", [], $order);
    }

    // ── DOCUMENT ENDPOINTS ────────────────────────────────────────

    public function createDocument(string $ndtcOrderId, array $payload, ?NdtcOrder $order = null): array
    {
        return $this->request('POST', "/api/v1/orders/{$ndtcOrderId}/documents", $payload, $order);
    }

    public function getDocument(string $ndtcOrderId, string $documentId, ?NdtcOrder $order = null): array
    {
        return $this->request('GET', "/api/v1/orders/{$ndtcOrderId}/documents/{$documentId}", [], $order);
    }

    public function deleteDocument(string $ndtcOrderId, string $documentId, ?NdtcOrder $order = null): void
    {
        $this->request('DELETE', "/api/v1/orders/{$ndtcOrderId}/documents/{$documentId}", [], $order);
    }

    // ── UPLOAD TO AWS S3 PRESIGNED URL ────────────────────────────
    // Unchanged — this goes directly to AWS, not through NDTC, so it's
    // outside the scope of this API-call logging (S3 responses aren't
    // NDTC API responses). Log separately later if desired.

    public function uploadToPresignedUrl(string $presignedUrl, array $fields, string $filePath): void
    {
        $multipart = [];

        foreach ($fields as $key => $value) {
            $multipart[] = ['name' => $key, 'contents' => $value];
        }

        $multipart[] = [
            'name'     => 'file',
            'contents' => fopen($filePath, 'r'),
            'filename' => basename($filePath),
        ];

        $response = Http::timeout(120)->asMultipart()->post($presignedUrl, $multipart);

        if ($response->status() >= 300) {
            Log::error('NDTC S3 upload failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('Document upload to S3 failed: ' . $response->body());
        }
    }

    // ── CLIENT PREFERENCES ────────────────────────────────────────

    public function setWebhookPreferences(string $payloadUrl, string $secret): array
    {
        return $this->request('POST', '/api/v1/clients/me/preferences', [
            'payloadUrl' => $payloadUrl,
            'secret'     => $secret,
        ]);
    }

    // ── CORE HTTP METHOD ──────────────────────────────────────────

    private function request(
        string $method,
        string $endpoint,
        array $payload = [],
    ): array {
        $url       = $this->baseUrl . $endpoint;
        $startedAt = microtime(true);

        $logData = [
            'ndtc_order_id'   => $this->resolveOrderId($endpoint),
            'endpoint'        => $endpoint,
            'request_payload' => $payload ?: null,
        ];

        try {
            $response = Http::withToken($this->auth->getToken())
                ->acceptJson()
                ->timeout(60)
                ->$method($url, $payload ?: null);

            if ($response->status() === 401) {
                Log::info('NDTC token expired — refreshing and retrying');
                $response = Http::withToken($this->auth->refreshToken())
                    ->acceptJson()
                    ->timeout(60)
                    ->$method($url, $payload ?: null);
            }

            $this->logCall($logData, $response->status(), $response->body(), null, $startedAt);

            if (!$response->successful()) {
                Log::error('NDTC API error', [
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'payload'  => $payload,
                ]);
                throw NdtcApiException::fromResponse($response->status(), $response->body());
            }

            return $response->json() ?? [];

        } catch (\Exception $e) {
            // Only log here if we haven't already logged a response above
            // (i.e. this is a network-level failure, not an HTTP error response)
            if (!isset($response)) {
                $this->logCall($logData, null, null, $e->getMessage(), $startedAt);
            }

            Log::error('NDTC API request failed', [
                'method'    => $method,
                'endpoint'  => $endpoint,
                'message'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function logCall(array $logData, ?int $status, ?string $body, ?string $errorMessage, float $startedAt): void
    {
        //if($status == 200) return;

        try {
            NdtcApiCallLog::create(array_merge($logData, [
                'response_status' => $status,
                'response_body'   => $body ? substr($body, 0, 65535) : null, // guard against extreme payloads
                'error_message'   => $errorMessage,
            ]));
        } catch (\Exception $e) {
            // Logging must never break the actual API call — just note it and move on
            Log::warning('Failed to write NdtcApiCallLog', ['error' => $e->getMessage()]);
        }
    }

    private function resolveOrderId(string $endpoint): ?string
    {
        if (!preg_match('#/orders/([a-f0-9]{24})#i', $endpoint, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
