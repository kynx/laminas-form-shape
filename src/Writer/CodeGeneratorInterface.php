<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Psalm\Type\Union;
use ReflectionClass;

interface CodeGeneratorInterface
{
    /**
     * @param array<ImportType> $importTypes
     */
    public function generate(
        ReflectionClass $reflection,
        Union $type,
        array $importTypes,
        string $contents,
        bool $replaceGetDataReturn = false
    ): GeneratedCode;
}
