<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Union;

use function array_filter;
use function array_map;
use function array_pop;
use function implode;
use function sort;

use const SORT_STRING;

final readonly class UnionDecorator
{
    private ArrayDecorator $arrayDecorator;
    private KeyedArrayDecorator $keyedArrayDecorator;

    public function __construct(public string $indentString = '    ', private ?int $literalLimit = null)
    {
        $this->arrayDecorator      = new ArrayDecorator($this);
        $this->keyedArrayDecorator = new KeyedArrayDecorator($this);
    }

    /**
     * @return non-empty-string
     */
    public function decorate(Union $union, int $indent = 0): string
    {
        if ($union->getAtomicTypes() === []) {
            throw DecoratorException::fromEmptyUnion();
        }

        $types = $this->combineTypes($this->getNonArrayUnion($union));

        foreach ($union->getAtomicTypes() as $type) {
            if ($type instanceof TArray) {
                $types[] = $this->arrayDecorator->decorate($type);
            } elseif ($type instanceof TKeyedArray) {
                $types[] = $this->keyedArrayDecorator->decorate($type, $indent);
            }
        }

        sort($types, SORT_STRING);

        return implode('|', $types);
    }

    private function getNonArrayUnion(Union $union): Union
    {
        return new Union(array_filter(
            $union->getAtomicTypes(),
            static fn (Atomic $type): bool => ! ($type instanceof TArray || $type instanceof TKeyedArray)
        ));
    }

    private function combineTypes(Union $union): array
    {
        $types = $union->getAtomicTypes();
        if ($types === []) {
            return [];
        }

        $combined = Type::combineUnionTypes(
            type_1:        new Union([array_pop($types)]),
            type_2:        new Union($types),
            literal_limit: $this->literalLimit ?? 500,
        );

        return array_map(
            static fn (Atomic $type): string => self::getTypeString($type),
            $combined->getAtomicTypes()
        );
    }

    private static function getTypeString(Atomic $type): string
    {
        return match ($type::class) {
            TLiteralFloat::class => $type->getId(false),
            TIntRange::class     => self::getIntRangeString($type),
            default              => $type->getId(),
        };
    }

    private static function getIntRangeString(TIntRange $range): string
    {
        if ($range->min_bound > 0 && $range->max_bound === null) {
            return 'positive-int';
        }
        if ($range->min_bound === null && $range->max_bound < 0) {
            return 'negative-int';
        }
        return $range->getId();
    }
}
