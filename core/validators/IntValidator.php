<?php

declare(strict_types=1);

namespace app\core\validators;

final class IntValidator extends AbstractValidator
{

    protected string $message = 'Field must be an integer.';

    public function validate(mixed $value, array $params = []): bool
    {
        return is_int($value) || is_string($value) && preg_match('/^-?\d+$/', $value);
    }

}