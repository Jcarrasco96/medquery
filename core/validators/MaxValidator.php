<?php

declare(strict_types=1);

namespace app\core\validators;

final class MaxValidator extends AbstractValidator
{

    protected string $message = 'Value must be greater than or equal {{max}}.';

    public function validate(mixed $value, array $params = []): bool
    {
        $max = $params['max'] ?? null;
        if ($max === null) {
            return true;
        }

        if (is_int($value) || is_float($value)) {
            return $value <= $max;
        }

        if (is_string($value) || is_array($value)) {
            return count((array)$value) <= $max;
        }

        return false;
    }

}