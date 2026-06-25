<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Self-contained FCM HTTP v1 dispatcher.
 *
 * Authenticates via Google OAuth2 using a service account JSON by:
 *  1. Building a JWT signed locally using openssl_sign (RS256).
 *  2. Exchanging the JWT for a short-lived Bearer token.
 *  3. Posting the FCM message to the REST v1 endpoint.
 *
 * No external package dependencies required.
 *
 * Credentials resolution order:
 *  1. storage/app/firebase/firebase_service_account.json
 *  2. Environment variables: FIREBASE_PROJECT_ID, FIREBASE_CLIENT_EMAIL, FIREBASE_PRIVATE_KEY
 */
class FcmService
{
    private ?string $projectId = null;
    private ?string $clientEmail = null;
    private ?string $privateKey = null;
    private ?string $cachedAccessToken = null;
    private int $tokenExpiresAt = 0;

    public function __construct()
    {
        $this->loadCredentials();
    }

    private function loadCredentials(): void
    {
        $jsonPath = storage_path('app/firebase/firebase_service_account.json');

        if (file_exists($jsonPath)) {
            try {
                $data = json_decode(file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
                $this->projectId  = $data['project_id'] ?? null;
                $this->clientEmail = $data['client_email'] ?? null;
                $this->privateKey  = $data['private_key'] ?? null;
                return;
            } catch (\Throwable) {
                Log::warning('[FcmService] Failed to parse firebase_service_account.json; falling back to env vars.');
            }
        }

        $this->projectId   = env('FIREBASE_PROJECT_ID');
        $this->clientEmail = env('FIREBASE_CLIENT_EMAIL');
        $this->privateKey  = str_replace('\\n', "\n", (string) env('FIREBASE_PRIVATE_KEY', ''));
    }

    public function isConfigured(): bool
    {
        return filled($this->projectId) && filled($this->clientEmail) && filled($this->privateKey);
    }

    // -----------------------------------------------------------------
    // Public send helpers
    // -----------------------------------------------------------------

    /**
     * Send a notification to a single FCM token.
     * Returns true on success, false on any error.
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (! $this->isConfigured()) {
            Log::info('[FcmService] Not configured — notification skipped.');
            return false;
        }

        $accessToken = $this->getAccessToken();
        if ($accessToken === null) {
            return false;
        }

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'joballocate_default',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ],
        ];

        if (! empty($data)) {
            $payload['message']['data'] = array_map('strval', $data);
        }

        return $this->post($accessToken, $payload);
    }

    /**
     * Send to multiple tokens (broadcasts across all devices for a user).
     */
    public function sendToTokens(array $fcmTokens, string $title, string $body, array $data = []): int
    {
        $successCount = 0;
        foreach ($fcmTokens as $token) {
            if (filled($token) && $this->sendToToken($token, $title, $body, $data)) {
                $successCount++;
            }
        }
        return $successCount;
    }

    // -----------------------------------------------------------------
    // OAuth2 JWT token exchange
    // -----------------------------------------------------------------

    private function getAccessToken(): ?string
    {
        if ($this->cachedAccessToken !== null && time() < $this->tokenExpiresAt) {
            return $this->cachedAccessToken;
        }

        $jwt = $this->buildJwt();
        if ($jwt === null) return null;

        $response = $this->httpPost('https://oauth2.googleapis.com/token', http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]), 'application/x-www-form-urlencoded');

        if ($response === null) return null;

        $decoded = json_decode($response, true);
        if (! isset($decoded['access_token'])) {
            Log::error('[FcmService] Token exchange failed: ' . $response);
            return null;
        }

        $this->cachedAccessToken = $decoded['access_token'];
        $this->tokenExpiresAt    = time() + (int) ($decoded['expires_in'] ?? 3600) - 60;

        return $this->cachedAccessToken;
    }

    private function buildJwt(): ?string
    {
        $now   = time();
        $scope = 'https://www.googleapis.com/auth/firebase.messaging';

        $header  = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'iss'   => $this->clientEmail,
            'scope' => $scope,
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ]));

        $signatureInput = "{$header}.{$payload}";
        $privateKey = openssl_pkey_get_private($this->privateKey);

        if ($privateKey === false) {
            Log::error('[FcmService] Failed to load private key.');
            return null;
        }

        $signature = '';
        if (! openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            Log::error('[FcmService] openssl_sign failed.');
            return null;
        }

        return "{$signatureInput}." . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // -----------------------------------------------------------------
    // HTTP helpers
    // -----------------------------------------------------------------

    private function post(string $accessToken, array $payload): bool
    {
        $url  = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $body = json_encode($payload);

        $response = $this->httpPost($url, $body, 'application/json', [
            "Authorization: Bearer {$accessToken}",
        ]);

        if ($response === null) return false;

        $decoded = json_decode($response, true);
        if (isset($decoded['name'])) return true;

        Log::warning('[FcmService] FCM send failed: ' . $response);
        return false;
    }

    private function httpPost(string $url, string $body, string $contentType, array $extraHeaders = []): ?string
    {
        $ch = curl_init($url);
        $headers = array_merge([
            "Content-Type: {$contentType}",
            'Content-Length: ' . strlen($body),
        ], $extraHeaders);

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $result = curl_exec($ch);
        $errno  = curl_errno($ch);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            Log::error("[FcmService] cURL error ({$errno}): {$error}");
            return null;
        }

        return $result ?: null;
    }
}
