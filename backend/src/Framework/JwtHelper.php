<?php

namespace App\Framework;

use RuntimeException;

class JwtHelper
{
    private const SECRET = 'fittrack-pro-jwt-secret-key';

    public static function encode(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $segments = [
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($payload)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), self::SECRET, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token format.');
        }

        [$header, $payload, $signature] = $parts;
        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $header . '.' . $payload, self::SECRET, true)
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Invalid token signature.');
        }

        $payloadData = json_decode(self::base64UrlDecode($payload), true);

        if (!is_array($payloadData)) {
            throw new RuntimeException('Invalid token payload.');
        }

        if (isset($payloadData['exp']) && time() > (int) $payloadData['exp']) {
            throw new RuntimeException('Token has expired.');
        }

        return $payloadData;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/'));
    }
}
