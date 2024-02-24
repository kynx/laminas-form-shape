<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\Writer\CodeGeneratorInterface;
use Kynx\Laminas\FormShape\Writer\GeneratedCode;
use Psalm\Type\Union;
use ReflectionClass;

final readonly class MockCodeGenerator implements CodeGeneratorInterface
{
    public function generate(
        ReflectionClass $reflection,
        Union $type,
        array $importTypes,
        string $contents,
        bool $replaceGetDataReturn = false
    ): GeneratedCode {
        return new GeneratedCode('TFoo', $contents . "\n// EDITED");
    }
}
