<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

use function array_map;
use function implode;
use function sort;
use function str_contains;

use const SORT_STRING;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
final readonly class InputType implements TypeNameInterface, TypeStringInterface
{
    /**
     * @param VisitedArray $types
     */
    public function __construct(private string $name, private array $types, private bool $optional = false)
    {
    }

    public function getTypeName(): string
    {
        if ($this->optional) {
            return $this->escapeName() . '?';
        }
        return $this->escapeName();
    }

    public function getTypeString(int $indent = 0, string $indentString = '    '): string
    {
        $types = array_map(
            static fn (TypeStringInterface $type): string => $type->getTypeString(),
            $this->types
        );

        sort($types, SORT_STRING);

        return implode('|', $types);
    }

    private function escapeName(): string
    {
        return str_contains($this->name, ' ') ? "'" . $this->name . "'" : $this->name;
    }
}
