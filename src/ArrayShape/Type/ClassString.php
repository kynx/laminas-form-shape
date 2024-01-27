<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

use function ltrim;

final readonly class ClassString extends AbstractParsedType
{
    /**
     * @param class-string $classString
     */
    public function __construct(public string $classString)
    {
    }

    public function getTypeString(int $indent = 0, string $indentString = '    '): string
    {
        return '\\' . ltrim($this->classString, '\\');
    }
}
