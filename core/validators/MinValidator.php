<?php

declare(strict_types=1);

namespace app\core\validators;

final class MinValidator extends AbstractValidator
{

    public string $message = 'Value must be at least {{min}}.';

    public function __construct(private readonly int $min = 0)
    {
    }

    public function validate(mixed $value, array $params = []): bool
    {
//        $min = $params['min'] ?? null;

//        if ($min === null) {
//            return true;
//        }

        if (is_int($value) || is_float($value)) {
            return $value >= $this->min;
        }

        if (is_string($value) || is_array($value)) {
            return count((array)$value) >= $this->min;
        }

        return false;
    }

    public function getMessage(): string
    {
        return str_replace('{{min}}', (string) $this->min, $this->message);
    }

}