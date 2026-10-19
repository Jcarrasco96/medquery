<?php

declare(strict_types=1);

namespace app\core\validators;

final class MaxValidator extends AbstractValidator
{

    public string $message = 'Value must be greater than or equal {{max}}.';

    public function __construct(private readonly int $max = 0)
    {
    }

    public function validate(mixed $value): bool
    {
//        $max = $params['max'] ?? null;
//        if ($max === null) {
//            return true;
//        }

        if (is_int($value) || is_float($value)) {
            return $value <= $this->max;
        }

        if (is_string($value) || is_array($value)) {
            return count((array)$value) <= $this->max;
        }

        return false;
    }

    public function getMessage(): string
    {
        return str_replace('{{max}}', (string) $this->max, $this->message);
    }

}