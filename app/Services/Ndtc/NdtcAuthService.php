<?php
// app/Services/Ndtc/NdtcAuthService.php

namespace App\Services\Ndtc;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NdtcAuthService
{
    private string $authUrl;
    private string $clientId;
    private string $clientSecret;
    private string $cacheKey = 'ndtc_access_token';

    public function __construct()
    {
        $this->authUrl      = config('ndtc.auth_url');
        $this->clientId     = config('ndtc.client_id');
        $this->clientSecret = config('ndtc.client_secret');
    }

    public function getToken(): string
    {
        // Cache for 23 hours — token valid for 24
        return Cache::remember($this->cacheKey, 82800, function () {
            return $this->fetchFreshToken();
        });
    }

    public function refreshToken(): string
    {
        Cache::forget($this->cacheKey);
        return $this->getToken();
    }

    private function fetchFreshToken(): string
    {
        $response = Http::asForm()
            ->timeout(30)
            ->post($this->authUrl, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

        if (!$response->successful()) {
            Log::error('NDTC auth failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('NDTC authentication failed: ' . $response->body());
        }

        $token = $response->json('access_token');

        if (empty($token)) {
            throw new \Exception('NDTC auth response missing access_token');
        }

        return $token;
    }
}
