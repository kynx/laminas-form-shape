<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TEmptyNumeric;
use Psalm\Type\Atomic\TFalse;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumeric;
use Psalm\Type\Atomic\TResource;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\MutableUnion;
use Psalm\Type\Union;

use function array_filter;
use function array_is_list;
use function array_map;
use function array_pop;
use function array_reduce;
use function assert;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_resource;
use function is_string;
use function str_contains;

final readonly class TypeUtil
{
    /**
     * Returns a union with only types found in `$filter` present
     */
    public static function filter(Union $union, Union $filter): Union
    {
        $builder = $union->getBuilder();

        foreach ($union->getAtomicTypes() as $unionType) {
            $contained = array_filter(
                $filter->getAtomicTypes(),
                static fn (Atomic $container): bool => TypeComparator::isContainedBy($unionType, $container)
            );
            if ($contained === []) {
                $builder->removeType($unionType->getKey());
            }
        }

        return $builder->freeze();
    }

    /**
     * Returns union of most specific types
     */
    public static function narrow(Union $search, Union $replace): Union
    {
        $builder = $search->getBuilder();

        foreach ($search->getAtomicTypes() as $searchType) {
            $builder->removeType($searchType->getKey());

            $narrowest = [];
            foreach ($replace->getAtomicTypes() as $replaceType) {
                $key  = self::getNarrowingKey($replaceType, $narrowest);
                $type = self::narrowestType($narrowest[$key] ?? $searchType, $replaceType);

                if ($type !== null) {
                    $narrowest[$key] = $type;
                }
            }

            foreach ($narrowest as $type) {
                $builder->addType($type);
            }
        }

        return $builder->freeze();
    }

    public static function widen(Union $search, Union $replace): Union
    {
        $builder = $search->getBuilder();

        foreach ($replace->getAtomicTypes() as $replaceType) {
            $found  = false;
            $widest = $replaceType;
            foreach ($builder->getAtomicTypes() as $searchType) {
                $type = self::widestType($searchType, $widest);
                if ($type !== null) {
                    $builder->removeType($searchType->getKey());
                    $builder->removeType($widest->getKey());
                    $builder->addType($type);
                    $widest = $type;
                    $found  = true;
                }
            }

            if (! $found) {
                $builder->addType($replaceType);
            }
        }

        return $builder->freeze();
    }

    public static function remove(Union $union, Union $remove): Union
    {
        $builder = $union->getBuilder();

        foreach (self::filter($union, $remove)->getAtomicTypes() as $type) {
            $builder->removeType($type->getKey());
        }

        return $builder->freeze();
    }

    /**
     * Returns union with one type replaced by one or more other types
     *
     * Unfortunately `Union::substitute()` always adds the replacement :(
     */
    public static function replaceType(Union $union, Atomic $search, Union $replace): Union
    {
        $builder = $union->getBuilder();
        $builder->removeType($search->getKey());

        if ($builder->equals($union->getBuilder())) {
            return $union;
        }

        return Type::combineUnionTypes($builder->freeze(), $replace);
    }

    /**
     * Returns union of Psalm types for `$value` for use in strictly-typed comparisons
     */
    public static function toStrictUnion(mixed $value): Union
    {
        return match (true) {
            is_array($value)    => new Union([self::toStrictArray($value)]),
            is_bool($value)     => new Union([$value ? new TTrue() : new TFalse()]),
            is_float($value)    => new Union([new TLiteralFloat($value)]),
            is_int($value)      => new Union([new TLiteralInt($value)]),
            is_object($value)   => new Union([new TNamedObject($value::class)]),
            is_string($value)   => new Union([TLiteralString::make($value)]),
            is_resource($value) => new Union([new TResource()]),
            $value === null     => new Union([new TNull()]),
            default             => new Union([new TMixed()]),
        };
    }

    /**
     * Returns union of Psalm types for `$value` for use in loosely-typed comparisons
     */
    public static function toLooseUnion(mixed $value): Union
    {
        return match (true) {
            is_array($value)    => new Union([self::toLooseArray($value)]),
            is_bool($value)     => self::toLooseBool($value),
            is_float($value)    => self::toLooseFloat($value),
            is_int($value)      => self::toLooseInt($value),
            is_object($value)   => new Union([new TNamedObject($value::class)]),
            is_string($value)   => self::toLooseString($value),
            is_resource($value) => self::toLooseResource(),
            $value === null     => self::toLooseBool(false),
            default             => new Union([new TMixed()]),
        };
    }

    public static function getEmptyUnion(): Union
    {
        $builder = new MutableUnion([new TString()]);
        $builder->removeType('string');
        return $builder->freeze();
    }

    /**
     * @param array<string, Atomic> $narrowest
     */
    private static function getNarrowingKey(Atomic $type, array $narrowest): string
    {
        $id = $type->getId(false);

        if (isset($narrowest[$id]) && self::isSpecific($narrowest[$id])) {
            return $type->getKey();
        }

        return $id;
    }

    private static function isSpecific(Atomic $type): bool
    {
        $key = $type->getKey();
        return str_contains($key, '(') || str_contains($key, '<');
    }

    private static function narrowestType(Atomic $type, Atomic $container): ?Atomic
    {
        if (TypeComparator::isContainedBy($type, $container)) {
            return $type;
        }
        if (TypeComparator::isContainedBy($container, $type)) {
            return $container;
        }

        return null;
    }

    private static function widestType(Atomic $type, Atomic $container): ?Atomic
    {
        if (TypeComparator::isContainedBy($type, $container)) {
            return $container;
        }
        if (TypeComparator::isContainedBy($container, $type)) {
            return $type;
        }

        return null;
    }

    private static function toStrictArray(array $value): TKeyedArray|TArray
    {
        if ($value === []) {
            return self::toLooseArray($value);
        }

        if (array_is_list($value)) {
            return Type::getNonEmptyListAtomic(self::combineTypes($value, true));
        }

        $properties = array_map(static fn (mixed $v): Union => self::toStrictUnion($v), $value);
        assert($properties !== []);

        return new TKeyedArray($properties);
    }

    private static function toLooseArray(array $value): TKeyedArray|TArray
    {
        if ($value === []) {
            return new TArray([new Union([new TArrayKey()]), new Union([new TMixed()])]);
        }

        if (array_is_list($value)) {
            return Type::getNonEmptyListAtomic(self::combineTypes($value, false));
        }

        $properties = array_map(static fn (mixed $v): Union => self::toLooseUnion($v), $value);
        assert($properties !== []);

        return new TKeyedArray($properties);
    }

    private static function toLooseBool(bool $value): Union
    {
        if ($value) {
            return new Union([
                new TFloat(), // can't make this more specific :(
                new TIntRange(null, -1),
                new TIntRange(1, null),
                new TNonEmptyString(),
                new TTrue(),
            ]);
        }

        return Type::combineUnionTypes(self::toStrictUnion($value), new Union([
            new TEmptyNumeric(),
            TLiteralString::make(""),
            new TNull(),
        ]));
    }

    private static function toLooseFloat(float $value): Union
    {
        if ($value === 0.0) {
            return self::toLooseBool(false);
        }

        $types = [
            new TLiteralFloat($value),
            TLiteralString::make("$value"),
        ];
        if ((string) $value === (int) $value . "") {
            $types[] = new TLiteralInt((int) $value);
        }

        return new Union($types);
    }

    private static function toLooseInt(int $value): Union
    {
        if ($value === 0) {
            return self::toLooseBool(false);
        }

        return new Union([
            new TLiteralInt($value),
            // new TLiteralFloat($value), // this would output 'float'
            TLiteralString::make("$value"),
        ]);
    }

    private static function toLooseString(string $value): Union
    {
        if ($value === '') {
            return self::toLooseBool(false);
        }

        $types = [
            TLiteralString::make($value),
        ];
        if ($value === (int) $value . "") {
            $types[] = new TLiteralInt((int) $value);
        }

        return new Union($types);
    }

    private static function toLooseResource(): Union
    {
        return new Union([
            new TResource(),
            new TNumeric(),
        ]);
    }

    private static function combineTypes(array $types, bool $strict): Union
    {
        assert($types !== []);

        /** @var mixed $type */
        $type  = array_pop($types);
        $union = $strict ? self::toStrictUnion($type) : self::toLooseUnion($type);
        if ($types === []) {
            return $union;
        }

        return array_reduce(
            $types,
            static function (Union $union, mixed $item) use ($strict): Union {
                return $strict
                    ? Type::combineUnionTypes($union, self::toStrictUnion($item))
                    : Type::combineUnionTypes($union, self::toLooseUnion($item));
            },
            $union
        );
    }
}
