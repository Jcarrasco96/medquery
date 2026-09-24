<?php

declare(strict_types=1);

namespace app\utils;

use app\core\App;
use app\models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

final class JwtService
{

    private const string KEY = 'CAMBIA_ESTO_POR_UN_SECRET_LARGO1';

    public function create(User $user): string
    {
        $time = time();

        $payload = [
            'iss' => App::$config['name'],
            'iat' => $time,
            'exp' => $time + 3600,

            'sub' => $user->id,

            'email' => $user->email,
        ];

        return JWT::encode($payload, self::KEY, 'HS256');
    }

    public function decode(string $token): stdClass
    {
        return JWT::decode($token, new Key(self::KEY, 'HS256'));
    }

}