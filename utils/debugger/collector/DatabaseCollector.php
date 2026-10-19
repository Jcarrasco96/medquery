<?php

declare(strict_types=1);

namespace app\utils\debugger\collector;

use app\utils\debugger\DebuggerCollectorInterface;

final class DatabaseCollector implements DebuggerCollectorInterface
{

    private static array $queries = [];

    public static function record(string $sql, float $durationMs, array $params = [], ?string $error = null): void
    {
        self::$queries[] = [
            'sql' => $sql,
            'params' => self::redact($params),
            'duration_ms' => round($durationMs, 3),
            'error' => $error,
        ];
    }

    public function getName(): string
    {
        return 'Database';
    }

    public function collect(): array
    {
        $totalMs = array_sum(
            array_column(self::$queries, 'duration_ms')
        );

        return [
            'total_queries' => count(self::$queries),
            'total_duration_ms' => round($totalMs, 3),
            'queries' => self::$queries,
        ];
    }

    private static function redact(array $params): array
    {
        $sensitive = [
            'password',
            'passwd',
            'token',
            'secret',
            'authorization',
            'api_key',
        ];

        foreach ($params as $key => $value) {
            if (is_string($key)) {
                foreach ($sensitive as $word) {
                    if (str_contains(strtolower($key), $word)) {
                        $params[$key] = '[REDACTED]';
                        continue 2;
                    }
                }
            }

            if (is_array($value)) {
                $params[$key] = self::redact($value);
            }
        }

        return $params;
    }

}