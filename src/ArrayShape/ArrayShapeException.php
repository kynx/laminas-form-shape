<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ExceptionInterface;
use Laminas\InputFilter\InputInterface;
use RuntimeException;

use function sprintf;

final class ArrayShapeException extends RuntimeException implements ExceptionInterface
{
    public static function noVisitorForInput(InputInterface $input): self
    {
        return new self(sprintf("No input visitor configured for '%s'", $input::class));
    }

    public static function cannotGetInputType(InputInterface $input): self
    {
        return new self(sprintf("Cannot get type for '%s'", $input->getName()));
    }
}
