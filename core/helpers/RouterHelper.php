<?php

declare(strict_types=1);

namespace app\core\helpers;

use Ramsey\Uuid\Uuid;

class RouterHelper
{

    public static function findConfiguredRoute(array $routes, string $method, string $path): array
    {
        if (!isset($routes[$method])) {
            return [];
        }

        foreach ($routes[$method] as $routePath => $handler) {
            $params = self::matchAndValidateRoute($routePath, $path);
            if ($params !== false) {
                return [
                    'handler' => $handler,
                    'params' => $params
                ];
            }
        }

        return [];
    }

    public static function matchAndValidateRoute(int|string $routePath, string $requestPath): false|array
    {
        $pattern = self::buildRoutePattern($routePath);

        if (preg_match($pattern, $requestPath, $matches)) {
            $params = [];
            $paramDefinitions = self::extractParamDefinitions($routePath);

            foreach ($paramDefinitions as $paramName => $paramType) {
                if (isset($matches[$paramName])) {
                    $value = $matches[$paramName];

                    if (!self::validateParamType($value, $paramType)) {
                        return false;
                    }

                    $params[$paramName] = self::castParamValue($value, $paramType);
                }
            }

            return $params;
        }

        return false;
    }

    private static function buildRoutePattern(int|string $routePath): string
    {
        $pattern = preg_replace_callback('/\{(\w+)(?::(\w+))?}/', function ($matches) {
            $paramType = $matches[2] ?? 'string';

            $patterns = [
                'int' => '(\d+)',
                'integer' => '(\d+)',
                'number' => '(\d+)',
                'float' => '([\d\.]+)',
                'string' => '([^/]+)',
                'slug' => '([a-z0-9-]+)',
                'alpha' => '([a-zA-Z]+)',
                'alphanumeric' => '([a-zA-Z0-9]+)',
                'base64' => '([A-Za-z0-9+\/]+={0,2})',
            ];

            $regex = $patterns[$paramType] ?? '([^/]+)';

            return "(?P<$matches[1]>$regex)";
        }, $routePath);

        return '#^' . str_replace('/', '\/', $pattern) . '$#';
    }

    private static function extractParamDefinitions(int|string $routePath): array
    {
        $definitions = [];

        preg_match_all('/\{(\w+)(?::(\w+))?}/', $routePath, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $paramName = $match[1];
            $paramType = $match[2] ?? 'string';
            $definitions[$paramName] = $paramType;
        }

        return $definitions;
    }

    private static function validateParamType(string $value, string $paramType): bool
    {
        return match ($paramType) {
            'int', 'integer' => ctype_digit($value),
            'float', 'number' => is_numeric($value),
            'alpha' => ctype_alpha($value),
            'alphanumeric' => ctype_alnum($value),
            'slug' => (bool)preg_match('/^[a-z0-9-]+$/', $value),
            'base64' => (bool)base64_decode($value, true),
            'uuid' => Uuid::isValid($value),
            default => true,
        };
    }

    private static function castParamValue(string $value, string $type): float|int|string
    {
        return match ($type) {
            'int', 'integer' => (int)$value,
            'float', 'number' => (float)$value,
            'uuid' => (string)Uuid::fromString($value),
            default => $value,
        };
    }

}