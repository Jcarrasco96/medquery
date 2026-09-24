<?php

declare(strict_types=1);

namespace app\core\validators;

abstract class AbstractValidator implements ValidatorInterface
{

    protected string $message = 'Invalid value';

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

}