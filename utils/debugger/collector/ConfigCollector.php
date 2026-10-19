<?php

declare(strict_types=1);

namespace app\utils\debugger\collector;

use app\core\App;
use app\utils\debugger\DebuggerCollectorInterface;

final class ConfigCollector implements DebuggerCollectorInterface
{

    public function getName(): string
    {
        return 'Config';
    }

    public function collect(): array
    {
        return App::$config;
    }
}