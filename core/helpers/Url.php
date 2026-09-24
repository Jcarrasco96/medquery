<?php

declare(strict_types=1);

namespace app\core\helpers;

class Url
{

    public static function to(string $path, array $query = []): string
    {
        $isHttps = ($_SERVER['HTTPS'] ?? '') === 'on'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            || ($_SERVER['x-forwarded-proto'] ?? '') === 'https';

        $port = $_SERVER['SERVER_PORT'] ?? 80;
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';

        $baseUrl = ($isHttps ? 'https' : 'http') . '://' . $serverName;

        if (($isHttps && $port != 443) || (!$isHttps && $port != 80)) {
            $baseUrl .= ":$port";
        }

        $baseUrl .= '/';

//        $baseUrl = "https://mysql-releases-ladies-chances.trycloudflare.com/";

        $pathParts = explode('?', $path, 2);
        $cleanPath = $pathParts[0];
        $existingQuery = [];

        if (isset($pathParts[1])) {
            parse_str($pathParts[1], $existingQuery);
        }

        $allQuery = array_merge($existingQuery, $query);

        $url = $baseUrl . ltrim($cleanPath, '/');

        if (!empty($allQuery)) {
            $url .= '?' . http_build_query($allQuery);
        }

        return $url;
    }

}