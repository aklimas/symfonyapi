<?php

namespace App\Controller;

use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTKey
{
    public const JWT_KEY = '12346';
    public const JWT_ALGORITHM = 'HS256';
    public const JWT_EXPIRED = 3600;

    public static function generateToken(User $user): string
    {
        $payload = [
            'userId' => $user->getId(),
            'exp' => time() + self::JWT_EXPIRED,
        ];

        return JWT::encode($payload, self::JWT_KEY, self::JWT_ALGORITHM);
    }

    public static function decode(string $token): \stdClass
    {
        $headers = new \stdClass();

        return JWT::decode(
            $token,
            new Key(JWTKey::JWT_KEY, JWTKey::JWT_ALGORITHM),
            $headers);
    }
}
