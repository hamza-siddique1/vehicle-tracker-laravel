<?php
// app/Services/Ndtc/NdtcApiService.php

namespace App\Services\Ndtc;

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

    public function createOrder(array $payload, string $transactionType = 'TNL'): array
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

    public function updateOrder(string $ndtcOrderId, array $payload): array
    {
        return $this->request('PUT', "/api/v3/clearinghouse/orders/{$ndtcOrderId}", $payload);
    }

    public function finalizeOrder(string $ndtcOrderId): array
    {
        return $this->request('POST', "/api/v3/clearinghouse/orders/{$ndtcOrderId}/finalize");
    }

    public function cancelOrder(string $ndtcOrderId): void
    {
        $this->request('DELETE', "/api/v3/clearinghouse/orders/{$ndtcOrderId}");
    }

    public function getOrder(string $ndtcOrderId): array
    {
        return $this->request('GET', "/api/v3/clearinghouse/orders/{$ndtcOrderId}");
    }

    // ── DOCUMENT ENDPOINTS ────────────────────────────────────────

    public function createDocument(string $ndtcOrderId, array $payload): array
    {
        return $this->request('POST', "/api/v1/orders/{$ndtcOrderId}/documents", $payload);
    }

    public function getDocument(string $ndtcOrderId, string $documentId): array
    {
        return $this->request('GET', "/api/v1/orders/{$ndtcOrderId}/documents/{$documentId}");
    }

    public function deleteDocument(string $ndtcOrderId, string $documentId): void
    {
        $this->request('DELETE', "/api/v1/orders/{$ndtcOrderId}/documents/{$documentId}");
    }

    // ── UPLOAD TO AWS S3 PRESIGNED URL ────────────────────────────
    // This goes directly to AWS — NOT through NDTC base URL

    public function uploadToPresignedUrl(string $presignedUrl, array $fields, string $filePath): void
    {
        $multipart = [];

        // Add all fields from NDTC create document response
        foreach ($fields as $key => $value) {
            $multipart[] = [
                'name'     => $key,
                'contents' => $value,
            ];
        }

        // File must be last
        $multipart[] = [
            'name'     => 'file',
            'contents' => fopen($filePath, 'r'),
            'filename' => basename($filePath),
        ];

        $response = Http::timeout(120)
            ->asMultipart()
            ->post($presignedUrl, $multipart);

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

    private function request(string $method, string $endpoint, array $payload = []): array
    {
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::withToken($this->auth->getToken())
                ->acceptJson()
                ->timeout(60)
                ->$method($url, $payload ?: null);

            // Token expired — refresh and retry once
            if ($response->status() === 401) {
                Log::info('NDTC token expired — refreshing and retrying');
                $response = Http::withToken($this->auth->refreshToken())
                    ->acceptJson()
                    ->timeout(60)
                    ->$method($url, $payload ?: null);
            }

            if (!$response->successful()) {
                Log::error('NDTC API error', [
                    'method'   => $method,
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'payload'  => $payload,
                ]);
                throw new \Exception(
                    "NDTC API error {$response->status()}: " . $response->body()
                );
            }

            return $response->json() ?? [];

        } catch (\Exception $e) {
            Log::error('NDTC API request failed', [
                'method'    => $method,
                'endpoint'  => $endpoint,
                'message'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
