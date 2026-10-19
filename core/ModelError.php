<?php

namespace app\core;

use app\core\validators\AbstractValidator;

interface ModelError
{

    public function getErrors(): array;

    public function getError(string $key): ?string;

    public function addError(string $attribute, string $message): void;

    public function clearError(string $attribute): void;
}