<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Validator;

use InvalidArgumentException;
use Kynx\Laminas\FormShape\ExceptionInterface;

use function get_debug_type;
use function is_string;
use function sprintf;

final class InvalidValidatorConfigurationException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param class-string $visitorClass
     */
    public static function forVisitor(string $visitorClass): self
    {
        return new self(sprintf(
            "Invalid configuration for validator-visitors: expected class-string<ValidatorVisitorInterface>, got '%s'",
            $visitorClass,
        ));
    }

    public static function forRegex(mixed $class): self
    {
        return new self(sprintf(
            "Invalid configuration for regex.patterns: expected class-string<Atomic>, got %s",
            is_string($class) ? $class : get_debug_type($class)
        ));
    }
}
