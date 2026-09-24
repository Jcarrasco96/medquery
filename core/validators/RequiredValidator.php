<?php

declare(strict_types=1);

namespace app\core\validators;

final class RequiredValidator extends AbstractValidator
{

    public function __construct(protected string $message = 'This field is required.')
    {
    }

    public function validate(mixed $value, array $params = []): bool
    {
        return !(is_null($value) || is_string($value) && trim($value) === '' || is_array($value) && empty($value));
    }

}