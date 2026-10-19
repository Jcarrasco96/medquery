<?php

declare(strict_types=1);

namespace app\core\validators;

interface ValidatorInterface
{

    public function validate(mixed $value): bool;

    public string $message {
        get;
    }

}