<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape;

use Kynx\Laminas\FormCli\ExceptionInterface;
use Laminas\InputFilter\InputInterface;
use RuntimeException;

use function sprintf;

final class ArrayShapeException extends RuntimeException implements ExceptionInterface
{
    public static function cannotParseInputType(InputInterface $input): self
    {
        return new self(sprintf("Cannot parse type for '%s'", $input->getName()));
    }
}
