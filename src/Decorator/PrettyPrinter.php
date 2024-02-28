<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Decorator;

use Kynx\Laminas\FormShape\DecoratorInterface;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Union;

use function array_filter;
use function array_map;
use function array_pop;
use function implode;
use function sort;
use function str_replace;
use function trim;

use const SORT_STRING;

final readonly class PrettyPrinter implements DecoratorInterface
{
    private ArrayDecorator $arrayDecorator;
    private KeyedArrayDecorator $keyedArrayDecorator;

    public function __construct(public string $indentString = '    ', private ?int $literalLimit = null)
    {
        $this->arrayDecorator      = new ArrayDecorator($this);
        $this->keyedArrayDecorator = new KeyedArrayDecorator($this);
    }

    public function decorate(Union $union, int $indent = 0): string
    {
        if ($union->getAtomicTypes() === []) {
            throw DecoratorException::fromEmptyUnion();
        }

        $types = $this->combineTypes($this->getNonArrayUnion($union));

        foreach ($union->getAtomicTypes() as $type) {
            if ($type instanceof TArray) {
                $types[] = $this->arrayDecorator->decorate($type, $indent);
            } elseif ($type instanceof TKeyedArray) {
                $types[] = $this->keyedArrayDecorator->decorate($type, $indent);
            }
        }

        sort($types, SORT_STRING);

        return implode('|', $types);
    }

    private function getNonArrayUnion(Union $union): ?Union
    {
        $types = array_filter(
            $union->getAtomicTypes(),
            static fn (Atomic $type): bool => ! ($type instanceof TArray || $type instanceof TKeyedArray)
        );

        if ($types === []) {
            return null;
        }

        return new Union($types);
    }

    /**
     * @return array<string>
     */
    private function combineTypes(?Union $union): array
    {
        if ($union === null) {
            return [];
        }

        $types = $union->getAtomicTypes();
        if ($types === []) {
            return [];
        }

        $last     = array_pop($types);
        $combined = $types === [] ? $union : Type::combineUnionTypes(
            type_1:        new Union([$last]),
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
            TLiteralFloat::class  => $type->getId(false),
            TLiteralString::class => self::getLiteralString($type),
            TIntRange::class      => self::getIntRangeString($type),
            default               => $type->getId(),
        };
    }

    private static function getLiteralString(TLiteralString $string): string
    {
        $value = trim($string->getId(), "'");
        return "'" . str_replace("'", "\'", $value) . "'";
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
