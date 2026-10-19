<?php

declare(strict_types=1);

namespace app\utils\debugger;

interface DebuggerCollectorInterface
{

    public function getName(): string;

    public function collect(): array;

}