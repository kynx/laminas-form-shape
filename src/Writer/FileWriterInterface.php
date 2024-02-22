<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Psalm\Type\Union;
use ReflectionClass;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
interface FileWriterInterface
{
    /**
     * @param array<ImportType> $importTypes
     * @return string The type name added to the file
     */
    public function write(
        ReflectionClass $reflection,
        Union $type,
        array $importTypes,
        bool $replaceGetDataReturn = false
    ): string;
}
