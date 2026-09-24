<?php

declare(strict_types=1);

namespace app\core;

use app\core\validators\AbstractValidator;
use DateTime;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

abstract class Model
{

    protected array $attributes = [];
    protected array $errors = [];

    public static function fromArray(array $data): static
    {
        $obj = new static();
        $reflection = new ReflectionClass($obj);

        foreach ($data as $key => $value) {
            if (!$reflection->hasProperty($key)) {
                continue;
            }

            $prop = $reflection->getProperty($key);

            try {
                $converted = self::castPropertyValue($prop, $value);

                $prop->setValue($obj, $converted);
            } catch (Exception $e) {
                App::$logger->throwable($e);
                $obj->addError($key, $e->getMessage());
            }
        }

        return $obj;
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);

        if (property_exists($this, 'attributes') && isset($this->attributes)) {
            foreach ($this->attributes as $key => $value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function __get(string $name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return null;
    }

    public function __set(string $name, $value): void
    {
        if (isset($this->$name)) {
            $this->$name = $value;
        } else {
            $this->attributes[$name] = $value;
        }
    }

    /**
     * @throws Exception
     */
    private static function castPropertyValue(ReflectionProperty $prop, mixed $raw): mixed
    {
        $type = $prop->getType();

        if ($type === null) {
            return $raw;
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $single) {
                try {
                    return self::castToSingleType($single, $raw);
                } catch (InvalidArgumentException) {

                }
            }
            throw new InvalidArgumentException("The value could not be converted to any of the union types for the property {$prop->getName()}");
        }

        return self::castToSingleType($type, $raw);
    }

    /**
     * @throws Exception
     */
    private static function castToSingleType(ReflectionNamedType $type, mixed $raw): mixed
    {
        $name = $type->getName();
        $allowsNull = $type->allowsNull();

        if ($raw === null) {
            if ($allowsNull) {
                return null;
            }
            throw new InvalidArgumentException("The property does not allow null");
        }

        return match ($name) {
            'int' => (int)$raw,
            'float' => (float)$raw,
            'string' => (string)$raw,
            'bool' => filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)$raw,
            'array' => (array)$raw,

            'DateTime' => new DateTime($raw),

            default => self::instantiateClass($name, $raw),
        };
    }

    /**
     * @throws ReflectionException
     */
    private static function instantiateClass(string $class, mixed $raw): object
    {
        if (class_exists($class) === false) {
            throw new InvalidArgumentException("Unknown type: $class");
        }

        if ($raw instanceof $class) {
            return $raw;
        }

        if (is_array($raw) && method_exists($class, 'fromArray')) {
            return $class::fromArray($raw);
        }

        $reflection = new ReflectionClass($class);
        $ctor = $reflection->getConstructor();

        if ($ctor && $ctor->getNumberOfParameters() === 1) {
            return $reflection->newInstance($raw);
        }

        throw new InvalidArgumentException("Could not create an instance of $class from the provided value");
    }

    public function rules(): array
    {
        return [];
    }

    public function validate(bool $clearErrors = true): bool
    {
        if ($clearErrors) {
            $this->errors = [];
        }

        foreach ($this->rules() as $attribute => $rawRules) {
            $value = $this->__get($attribute);

            $rawRules = is_array($rawRules) ? $rawRules : [$rawRules];

            foreach ($rawRules as $rule) {
                if (!$rule instanceof AbstractValidator) {
                    continue;
                }

                if (!$rule->validate($value)) {
                    $this->addError($attribute, $rule->getMessage());
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getError(string $key): ?string
    {
        return $this->errors[$key] ?? null;
    }

    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute] = $message;
    }

}