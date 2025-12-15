<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Psalm\Type\Union;
use ReflectionClass;

use function file_get_contents;
use function file_put_contents;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class FileWriter implements FileWriterInterface
{
    public function __construct(private CodeGeneratorInterface $codeGenerator)
    {
    }

    /**
     * @param array<ImportType> $importTypes
     */
    public function write(
        ReflectionClass $reflection,
        Union $type,
        array $importTypes,
        bool $replaceGetDataReturn = false
    ): string {
        $contents  = (string) file_get_contents($reflection->getFileName());
        $generated = $this->codeGenerator->generate($reflection, $type, $importTypes, $contents, $replaceGetDataReturn);
        file_put_contents($reflection->getFileName(), $generated->contents);

        return $generated->type;
    }
}
