<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TFalse;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TResource;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\Union;

use function array_combine;
use function array_filter;
use function array_is_list;
use function array_keys;
use function array_map;
use function array_pop;
use function assert;
use function count;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_numeric;
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
     * Returns union with any occurrences of type `$search` replaced by `$replace`
     */
    public static function replace(Union $union, Atomic $search, Union $replace): Union
    {
        if (self::hasType($union, $search)) {
            $builder = $union->getBuilder();
            return $builder->substitute(new Union([$search]), $replace)->freeze();
        }

        return $union;
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
     * Returns Psalm type for given PHP value
     */
    public static function toType(mixed $value): Atomic
    {
        return match (true) {
            is_array($value)    => self::toArrayType($value),
            is_bool($value)     => $value ? new TTrue() : new TFalse(),
            is_float($value)    => new TFloat(),
            is_int($value)      => new TInt(),
            is_object($value)   => new TNamedObject($value::class),
            is_numeric($value)  => new TNumericString(),
            is_string($value)   => $value === '' ? new TString() : new TNonEmptyString(),
            is_resource($value) => new TResource(),
            default             => new TMixed(),
            $value === null     => new TNull(),
        };
    }

    /**
     * Returns Psalm literal for PHP value, if possible, otherwise Psalm type
     */
    public static function toLiteralType(mixed $value): Atomic
    {
        return match (true) {
            is_array($value)  => self::toLiteralArrayType($value),
            is_float($value)  => new TLiteralFloat($value),
            is_int($value)    => new TLiteralInt($value),
            is_string($value) => TLiteralString::make($value),
            default           => self::toType($value),
        };
    }

    private static function toArrayType(array $value): TKeyedArray|TArray
    {
        if ($value === []) {
            return new TArray([new Union([new TArrayKey()]), new Union([new TMixed()])]);
        }

        $types = array_map(static fn (mixed $item): Atomic => self::toType($item), $value);
        $last  = array_pop($types);
        if ($types === []) {
            $union = new Union([$last]);
        } else {
            $union = Type::combineUnionTypes(new Union([$last]), new Union($types));
        }

        if (array_is_list($value)) {
            return Type::getNonEmptyListAtomic($union);
        }

        /** @var non-empty-array<Atomic> $keys */
        $keys = array_map(
            static fn (mixed $key): TInt|TString => is_int($key) ? new TInt() : new TString(),
            array_keys($value)
        );
        if (count($keys) > 1) {
            $keys = [new TArrayKey()];
        }

        return new TNonEmptyArray([new Union($keys), $union]);
    }

    private static function toLiteralArrayType(array $value): TKeyedArray|TArray
    {
        if ($value === []) {
            return self::toArrayType($value);
        }

        $literals = array_map(static fn (mixed $item): Atomic => self::toLiteralType($item), $value);
        $last     = array_pop($literals);
        if ($literals === []) {
            $union = new Union([$last]);
        } else {
            $union = Type::combineUnionTypes(new Union([$last]), new Union($literals));
        }
        if (array_is_list($value)) {
            return Type::getNonEmptyListAtomic($union);
        }

        $properties = array_combine(array_keys($value), array_map(
            static fn (Atomic $type): Union => new Union([$type]),
            $union->getAtomicTypes()
        ));
        assert($properties !== []);

        return new TKeyedArray($properties);
    }
}
