<?php

declare(strict_types=1);

namespace app\core\validators;

final class IntValidator extends AbstractValidator
{

    public string $message = 'Field must be an integer.';

    public function validate(mixed $value): bool
    {
        return is_int($value) || is_string($value) && preg_match('/^-?\d+$/', $value);
    }

}