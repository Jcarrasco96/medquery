<?php

declare(strict_types=1);

namespace app\core\validators;

abstract class AbstractValidator implements ValidatorInterface
{

    public string $message = 'Invalid value' {
        get {
            return $this->message;
        }
        set {
            $this->message = $value;
        }
    }

    public function getMessage(): string
    {
        return $this->message;
    }

}