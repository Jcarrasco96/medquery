<?php

declare(strict_types=1);

namespace app\core\validators;

final class EmailValidator extends AbstractValidator
{

    public function __construct(protected string $message = 'Email format is not valid.')
    {
    }

    public function validate(mixed $value, array $params = []): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL);
    }

}