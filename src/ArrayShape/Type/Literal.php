<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

use function array_filter;
use function array_map;
use function array_values;
use function implode;
use function in_array;
use function is_int;
use function is_string;
use function sort;

use const SORT_STRING;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
final readonly class Literal extends AbstractVisitedType
{
    /**
     * @param array<int|string> $values
     */
    public function __construct(private array $values)
    {
    }

    public function getTypeString(int $indent = 0, string $indentString = '    '): string
    {
        $values = array_map(
            static fn (string|int $value): string|int => is_string($value) ? "'$value'" : $value,
            $this->values
        );

        sort($values, SORT_STRING);

        return implode('|', $values);
    }

    /**
     * @param VisitedArray $types
     */
    public function matches(array $types): bool
    {
        $valueTypes = $this->getTypes();

        return PsalmType::filter($valueTypes, $types) !== [];
    }

    /**
     * @param VisitedArray $types
     */
    public function withTypes(array $types): self
    {
        $hasString = PsalmType::hasStringType($types);
        $hasInt    = PsalmType::hasIntType($types);
        $filtered  = array_filter(
            $this->values,
            static fn (string|int $value): bool => (is_string($value) && $hasString) || (is_int($value) && $hasInt)
        );

        return new self(array_values($filtered));
    }

    /**
     * @return list<PsalmType>
     */
    private function getTypes(): array
    {
        $types = [];
        foreach ($this->values as $value) {
            if (is_string($value) && ! in_array(PsalmType::String, $types, true)) {
                $types[] = PsalmType::String;
            } elseif (! in_array(PsalmType::Int, $types, true)) {
                $types[] = PsalmType::Int;
            }
        }

        return $types;
    }
}
