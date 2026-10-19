<?php

declare(strict_types=1);

namespace app\utils\debugger\collector;

use app\core\App;
use app\utils\debugger\DebuggerCollectorInterface;

final class PerformanceCollector implements DebuggerCollectorInterface
{

    private int $startTime;
    private int $startMemory;

    public function __construct()
    {
        $this->startTime = App::$startTime; //hrtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    public function getName(): string
    {
        return 'Performance';
    }

    public function collect(): array
    {
        return [
            'elapsed_ms' => round((hrtime(true) - $this->startTime) / 1_000_000, 3),
            'memory_current_bytes' => memory_get_usage(true),
            'memory_peak_bytes' => memory_get_peak_usage(true),
            'memory_start_bytes' => $this->startMemory,
            'php_version' => PHP_VERSION,
        ];
    }

}