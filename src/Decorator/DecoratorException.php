<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\ExceptionInterface;
use RuntimeException;

final class DecoratorException extends RuntimeException implements ExceptionInterface
{
    public static function fromEmptyUnion(): self
    {
        return new self("Cannot decorate empty union");
    }
}
