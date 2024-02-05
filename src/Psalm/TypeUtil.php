<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TEmptyNumeric;
use Psalm\Type\Atomic\TFalse;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyScalar;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumeric;
use Psalm\Type\Atomic\TResource;
use Psalm\Type\Atomic\TTrue;
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
     * Returns union with any types not found in `$filter` removed
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
     * Returns union containing types from `$replace` that are more specific than a type from `$search`
     *
     * If the `$preserve` option is true, non-matching entries from `$search` will be preserved.
     */
    public static function narrow(Union $search, Union $replace, bool $preserve = false): Union
    {
        $builder = $replace->getBuilder();

        foreach ($replace->getAtomicTypes() as $replaceType) {
            $contained = array_filter(
                $search->getAtomicTypes(),
                static fn (Atomic $searchType): bool => TypeComparator::isContainedBy($replaceType, $searchType)
                    || TypeComparator::isContainedBy($searchType, $replaceType)
            );
            if ($contained === []) {
                $builder->removeType($replaceType->getKey());
            }
        }

        if (! $preserve) {
            return $builder->freeze();
        }

        foreach ($search->getAtomicTypes() as $searchType) {
            $contained = array_filter(
                $builder->getAtomicTypes(),
                static fn (Atomic $replaceType): bool => TypeComparator::isContainedBy($replaceType, $searchType)
                    || TypeComparator::isContainedBy($searchType, $replaceType)
            );
            if ($contained === []) {
                $builder->addType($searchType);
            }
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
     * Returns true if `$union` has any types equivalent to `$type`
     *
     * Simple types (scalars, arrays) are considered equivalent if one of the `$union` types is an `instance of $type`.
     * Complex types (for example, `TNamedObject`) defer to `Atomic::equals()` for the equivalence check.
     */
    public static function hasType(Union $union, Atomic $container): bool
    {
        foreach ($union->getAtomicTypes() as $unionType) {
            if (TypeComparator::isContainedBy($unionType, $container)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if type is a literal
     */
    public static function isLiteral(Atomic $type): bool
    {
        return str_contains($type->getId(), '(');
    }

    /**
     * Returns Psalm literal for PHP value, if possible, otherwise Psalm type
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
     * Returns Psalm type for given PHP value
     */
    public static function toLaxUnion(mixed $value): Union
    {
        return match (true) {
            is_array($value)    => new Union([self::toLaxArray($value)]),
            is_bool($value)     => self::toLaxBool($value),
            is_float($value)    => self::toLaxFloat($value),
            is_int($value)      => self::toLaxInt($value),
            is_object($value)   => new Union([new TNamedObject($value::class)]),
            is_string($value)   => self::toLaxString($value),
            is_resource($value) => self::toLaxResource(),
            $value === null     => self::toLaxBool(false),
            default             => new Union([new TMixed()]),
        };
    }

    private static function toStrictArray(array $value): TKeyedArray|TArray
    {
        if ($value === []) {
            return self::toLaxArray($value);
        }

        if (array_is_list($value)) {
            return Type::getNonEmptyListAtomic(self::combineTypes($value, true));
        }

        $properties = array_map(static fn (mixed $v): Union => self::toStrictUnion($v), $value);
        assert($properties !== []);

        return new TKeyedArray($properties);
    }

    private static function toLaxArray(array $value): TKeyedArray|TArray
    {
        if ($value === []) {
            return new TArray([new Union([new TArrayKey()]), new Union([new TMixed()])]);
        }

        if (array_is_list($value)) {
            return Type::getNonEmptyListAtomic(self::combineTypes($value, false));
        }

        $properties = array_map(static fn (mixed $v): Union => self::toLaxUnion($v), $value);
        assert($properties !== []);

        return new TKeyedArray($properties);
    }

    private static function toLaxBool(bool $value): Union
    {
        if ($value) {
            return new Union([new TNonEmptyScalar()]);
        }

        return Type::combineUnionTypes(self::toStrictUnion($value), new Union([
            new TEmptyNumeric(),
            TLiteralString::make(""),
            new TNull(),
        ]));
    }

    private static function toLaxFloat(float $value): Union
    {
        if ($value === 0.0) {
            return self::toLaxBool(false);
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

    private static function toLaxInt(int $value): Union
    {
        if ($value === 0) {
            return self::toLaxBool(false);
        }

        return new Union([
            new TLiteralInt($value),
            // new TLiteralFloat($value), // this would output 'float'
            TLiteralString::make("$value"),
        ]);
    }

    private static function toLaxString(string $value): Union
    {
        if ($value === '') {
            return self::toLaxBool(false);
        }

        $types = [
            TLiteralString::make($value),
        ];
        if ($value === (int) $value . "") {
            $types[] = new TLiteralInt((int) $value);
        }

        return new Union($types);
    }

    private static function toLaxResource(): Union
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
        $union = $strict ? self::toStrictUnion($type) : self::toLaxUnion($type);
        if ($types === []) {
            return $union;
        }

        return array_reduce(
            $types,
            static function (Union $union, mixed $item) use ($strict): Union {
                return $strict
                    ? Type::combineUnionTypes($union, self::toStrictUnion($item))
                    : Type::combineUnionTypes($union, self::toLaxUnion($item));
            },
            $union
        );
    }
}
