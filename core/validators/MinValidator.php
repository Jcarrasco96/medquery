<?php

declare(strict_types=1);

namespace app\core\validators;

final class MinValidator extends AbstractValidator
{

    protected string $message = 'Value must be at least {{min}}.';

    public function validate(mixed $value, array $params = []): bool
    {
        $min = $params['min'] ?? null;

        if ($min === null) {
            return true;
        }

        if (is_int($value) || is_float($value)) {
            return $value >= $min;
        }

        if (is_string($value) || is_array($value)) {
            return count((array)$value) >= $min;
        }

        return false;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

}