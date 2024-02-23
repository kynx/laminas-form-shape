<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\Writer\FileWriterInterface;
use Psalm\Type\Union;
use ReflectionClass;

/**
 * @psalm-type TWrittenFile = array{
 *      reflection:      ReflectionClass,
 *      type:            Union,
 *      imports:         array<ImportType>,
 *      replace-getdata: bool
 * }
 */
final class MockWriter implements FileWriterInterface
{
    /** @var array<TWrittenFile> */
    public array $written = [];

    public function write(
        ReflectionClass $reflection,
        Union $type,
        array $importTypes,
        bool $replaceGetDataReturn = false
    ): string {
        $this->written[] = [
            'reflection'     => $reflection,
            'type'           => $type,
            'imports'        => $importTypes,
            'remove-getdata' => $replaceGetDataReturn,
        ];

        return 'T' . $reflection->getShortName() . 'Array';
    }
}
