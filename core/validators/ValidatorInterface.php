<?php

declare(strict_types=1);

namespace app\core\validators;

interface ValidatorInterface
{

    public function validate(mixed $value, array $params = []): bool;

    public function getMessage(): string;

}