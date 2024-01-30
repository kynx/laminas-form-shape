<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Type;

use function ltrim;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
final readonly class ClassString extends AbstractVisitedType
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

    /**
     * @param VisitedArray $types
     */
    public function matches(array $types): bool
    {
        foreach ($types as $type) {
            if ($type instanceof ClassString && $type->classString === $this->classString) {
                return true;
            }
        }

        return false;
    }
}
