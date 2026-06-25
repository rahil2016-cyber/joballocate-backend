<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseTokenVerifier
{
    private const GOOGLE_CERT_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    /**
     * Verifies a Firebase ID token.
     * Returns an array of verified claims (including 'uid', 'phone_number') or null on failure.
     *
     * @param string $token
     * @return array|null
     */
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            Log::warning('[FirebaseTokenVerifier] Token does not have 3 parts.');
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header = json_decode(self::base64UrlDecode($headerB64), true);
        $payload = json_decode(self::base64UrlDecode($payloadB64), true);

        if (!$header || !$payload) {
            Log::warning('[FirebaseTokenVerifier] Failed to decode header or payload.');
            return null;
        }

        // 1. Verify Header Algorithm and Key ID
        if (($header['alg'] ?? null) !== 'RS256') {
            Log::warning('[FirebaseTokenVerifier] Invalid algorithm: ' . ($header['alg'] ?? 'none'));
            return null;
        }

        $kid = $header['kid'] ?? null;
        if (!$kid) {
            Log::warning('[FirebaseTokenVerifier] Missing kid in header.');
            return null;
        }

        // 2. Verify Payload Claims
        $projectId = env('FIREBASE_PROJECT_ID', 'joballocate');

        // Audience check
        if (($payload['aud'] ?? null) !== $projectId) {
            Log::warning('[FirebaseTokenVerifier] Audience mismatch. Expected: ' . $projectId . ', Got: ' . ($payload['aud'] ?? 'none'));
            return null;
        }

        // Issuer check
        $expectedIssuer = 'https://securetoken.google.com/' . $projectId;
        if (($payload['iss'] ?? null) !== $expectedIssuer) {
            Log::warning('[FirebaseTokenVerifier] Issuer mismatch. Expected: ' . $expectedIssuer . ', Got: ' . ($payload['iss'] ?? 'none'));
            return null;
        }

        $now = time();

        // Expiration check (clock tolerance: 60s)
        if (isset($payload['exp']) && ($payload['exp'] + 60) < $now) {
            Log::warning('[FirebaseTokenVerifier] Token expired. Exp: ' . $payload['exp'] . ', Now: ' . $now);
            return null;
        }

        // Issued-at check (clock tolerance: 60s)
        if (isset($payload['iat']) && ($payload['iat'] - 60) > $now) {
            Log::warning('[FirebaseTokenVerifier] Token issued in future. Iat: ' . $payload['iat'] . ', Now: ' . $now);
            return null;
        }

        // Subject check
        if (empty($payload['sub'])) {
            Log::warning('[FirebaseTokenVerifier] Missing subject (uid).');
            return null;
        }

        // 3. Verify Signature
        $certs = self::getGooglePublicCertificates();
        if (!$certs || !isset($certs[$kid])) {
            Log::warning('[FirebaseTokenVerifier] Public key not found for kid: ' . $kid);
            return null;
        }

        $publicKeyPem = $certs[$kid];
        $signatureInput = $headerB64 . '.' . $payloadB64;
        $signature = self::base64UrlDecode($signatureB64);

        $result = openssl_verify(
            $signatureInput,
            $signature,
            $publicKeyPem,
            OPENSSL_ALGO_SHA256
        );

        if ($result !== 1) {
            Log::warning('[FirebaseTokenVerifier] Signature verification failed.');
            return null;
        }

        return [
            'uid' => $payload['sub'],
            'phone_number' => $payload['phone_number'] ?? null,
            'email' => $payload['email'] ?? null,
            'name' => $payload['name'] ?? null,
        ];
    }

    /**
     * Retrieve Google's public certificates (cached for 6 hours).
     *
     * @return array|null
     */
    private static function getGooglePublicCertificates(): ?array
    {
        return Cache::remember('google_firebase_certs', 21600, function () {
            try {
                $response = Http::timeout(10)->get(self::GOOGLE_CERT_URL);
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Throwable $e) {
                Log::error('[FirebaseTokenVerifier] Failed to retrieve Google certificates: ' . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Helper to decode Base64Url strings.
     */
    private static function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
