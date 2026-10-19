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

        return JWT::encode($payload, App::$config['jwt_secret'], 'HS256');
    }

    public static function decode(string $token): stdClass
    {
        return JWT::decode($token, new Key(App::$config['jwt_secret'], 'HS256'));
    }

    public static function encode(array $payload): string
    {
        return JWT::encode($payload, App::$config['jwt_secret'], 'HS256');
    }

}