<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\ExceptionInterface;
use ReflectionClass;
use RuntimeException;
use Throwable;

use function sprintf;

final class CodeGeneratorException extends RuntimeException implements ExceptionInterface
{
    public static function cannotParse(ReflectionClass $reflection, Throwable $throwable): self
    {
        return new self(
            sprintf("Could not parse %s: %s", $reflection->getFileName(), $throwable->getMessage()),
            0,
            $throwable
        );
    }

    public static function classNotFound(ReflectionClass $reflection): self
    {
        return new self(sprintf(
            "Could not find class %s in file %s",
            $reflection->getName(),
            $reflection->getFileName()
        ));
    }
}
