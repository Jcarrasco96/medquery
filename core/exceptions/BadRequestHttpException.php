<?php

declare(strict_types=1);

namespace app\core\exceptions;

use Exception;
use Throwable;

final class BadRequestHttpException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }

}