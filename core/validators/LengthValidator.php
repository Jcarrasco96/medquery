<?php

namespace app\core\validators;

final class LengthValidator extends AbstractValidator
{

    public string $message = 'This field is required to {{length}} characters.';

    public function __construct(private readonly int $length)
    {
    }

    public function validate(mixed $value): bool
    {
        return count((array)$value) <= $this->length;
    }

    public function getMessage(): string
    {
        return str_replace('{{length}}', (string) $this->length, $this->message);
    }

}