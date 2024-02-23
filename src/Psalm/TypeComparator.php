<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyScalar;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TObject;
use Psalm\Type\Atomic\TScalar;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\Union;

use function array_filter;
use function array_merge;
use function array_reduce;
use function assert;
use function count;
use function in_array;
use function is_a;
use function is_int;
use function is_numeric;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * @internal
 *
 * @psalm-internal \Kynx\Laminas\FormShape
 * @psalm-internal \KynxTest\Laminas\FormShape
 */
final readonly class TypeComparator
{
    /**
     * Returns true if `$type` is contained by `$container` type
     *
     * Naive version of Psalm's internal `AtomicTypeComparator::isContainedBy()`
     */
    public static function isContainedBy(Atomic $type, Atomic $container): bool
    {
        if ($container instanceof TMixed) {
            return true;
        }

        if (self::isContainedByLiteral($type, $container)) {
            return true;
        }

        if (self::isContainedByInheritance($type, $container)) {
            return true;
        }

        if (self::isContainedByInt($type, $container)) {
            return true;
        }

        if (self::isContainedByString($type, $container)) {
            return true;
        }

        if (self::isContainedByArrayKey($type, $container)) {
            return true;
        }

        if (self::isContainedByScalar($type, $container)) {
            return true;
        }

        if (self::isContainedByNamedObject($type, $container)) {
            return true;
        }

        if (self::isContainedByArray($type, $container)) {
            return true;
        }

        if (self::isContainedByKeyedArray($type, $container)) {
            return true;
        }

        return false;
    }

    private static function isContainedByLiteral(Atomic $type, Atomic $container): bool
    {
        if (! (self::isLiteral($type) && self::isLiteral($container))) {
            return false;
        }
        if (! $type instanceof $container) {
            return false;
        }

        return $type->value === $container->value;
    }

    private static function isContainedByInheritance(Atomic $type, Atomic $container): bool
    {
        if (
            $type instanceof TArray
            || $type instanceof TInt
            || $type instanceof TKeyedArray
            || $type instanceof TNamedObject
            || $type instanceof TString
        ) {
            return false;
        }

        if ($type instanceof TFloat && $container instanceof TLiteralFloat) {
            return false;
        }

        if ($type instanceof TInt && $container instanceof TInt) {
            return self::isContainedByInt($type, $container);
        }

        return $type instanceof $container;
    }

    private static function isContainedByArrayKey(Atomic $type, Atomic $container): bool
    {
        if (! $container instanceof TArrayKey) {
            return false;
        }

        return $type instanceof TInt || $type instanceof TString;
    }

    private static function isContainedByInt(Atomic $type, Atomic $container): bool
    {
        if (! ($type instanceof TInt && $container instanceof TInt)) {
            return false;
        }
        if ($container instanceof TLiteralInt) {
            return false;
        }
        if (! $container instanceof TIntRange) {
            return true;
        }

        if ($type instanceof TIntRange) {
            $min = $type->min_bound ?? PHP_INT_MIN;
            $max = $type->max_bound ?? PHP_INT_MAX;
        } else {
            $min = PHP_INT_MIN;
            $max = PHP_INT_MAX;
        }

        // phpcs:disable WebimpressCodingStandard.Formatting.RedundantParentheses.SingleEquality
        return $min >= ($container->min_bound ?? PHP_INT_MIN)
            && $max <= ($container->max_bound ?? PHP_INT_MAX);
        // phpcs:enable
    }

    private static function isContainedByString(Atomic $type, Atomic $container): bool
    {
        if (! ($type instanceof TString && $container instanceof TString)) {
            return false;
        }
        if ($type instanceof TLiteralString && $container instanceof TLiteralString) {
            return $type->value === $container->value;
        }

        if ($type::class === $container::class) {
            return true;
        }
        if ($type instanceof TLiteralString && $container instanceof TNumericString) {
            return is_numeric($type->value);
        }
        if ($type instanceof TLiteralString && $container instanceof TNonEmptyString) {
            return $type->value !== '';
        }
        if ($type instanceof TNumericString && $container instanceof TNonEmptyString) {
            return true;
        }

        return $type::class !== TString::class && $container::class === TString::class;
    }

    private static function isContainedByScalar(Atomic $type, Atomic $container): bool
    {
        if (! $container instanceof TScalar) {
            return false;
        }

        if ($container instanceof TNonEmptyScalar) {
            if ($type instanceof TLiteralString && $type->value !== '') {
                return true;
            }
            if ($type instanceof TIntRange && ($type->max_bound < 0 || $type->min_bound > 0)) {
                return true;
            }

            return $type instanceof TNonEmptyString
                || $type instanceof TTrue;
        }

        return $type instanceof TBool
            || $type instanceof TInt
            || $type instanceof TFloat
            || $type instanceof TString;
    }

    private static function isContainedByNamedObject(Atomic $type, Atomic $container): bool
    {
        if ($type instanceof TNamedObject && $container instanceof TObject) {
            return true;
        }

        if (! ($type instanceof TNamedObject && $container instanceof TNamedObject)) {
            return false;
        }

        if (! $type instanceof TGenericObject && $container instanceof TGenericObject) {
            return false;
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        if (! is_a($type->value, $container->value, true)) {
            return false;
        }

        if (! ($type instanceof TGenericObject && $container instanceof TGenericObject)) {
            return true;
        }

        $joinParams = static fn (Union $union, Union $param): Union => Type::combineUnionTypes($union, $param);

        $containerUnion = array_reduce($container->type_params, $joinParams, TypeUtil::getEmptyUnion());
        $typeUnion      = array_reduce($type->type_params, $joinParams, TypeUtil::getEmptyUnion());

        $matched = [];
        foreach ($typeUnion->getAtomicTypes() as $key => $typeParam) {
            foreach ($containerUnion->getAtomicTypes() as $containerParam) {
                if (self::isContainedBy($typeParam, $containerParam)) {
                    $matched[$key] = $typeParam;
                    break;
                }
            }
        }

        return $typeUnion->getAtomicTypes() === $matched;
    }

    private static function isContainedByArray(Atomic $type, Atomic $container): bool
    {
        if ($type instanceof TKeyedArray) {
            return self::isKeyedArrayContainedByArray($type, $container);
        }

        if (! ($type instanceof TArray && $container instanceof TArray)) {
            return false;
        }

        if ($type instanceof TNonEmptyArray && $container instanceof TNonEmptyArray) {
            if ($type->count !== $container->count) {
                return false;
            }
        } elseif ($container instanceof TNonEmptyArray) {
            return false;
        }

        if (count($type->type_params) !== count($container->type_params)) {
            return false;
        }

        [$typeKeyUnion, $typeValueUnion]           = $type->type_params;
        [$containerKeyUnion, $containerValueUnion] = $container->type_params;

        $all = $matched = [];

        foreach ($typeKeyUnion->getAtomicTypes() as $firstKeyType) {
            $all[0][] = $firstKeyType;
            foreach ($containerKeyUnion->getAtomicTypes() as $secondKeyType) {
                if (self::isContainedBy($firstKeyType, $secondKeyType)) {
                    $matched[0][] = $firstKeyType;
                    break;
                }
            }
        }
        foreach ($typeValueUnion->getAtomicTypes() as $firstValueType) {
            $all[1][] = $firstValueType;
            foreach ($containerValueUnion->getAtomicTypes() as $secondValueType) {
                if (self::isContainedBy($firstValueType, $secondValueType)) {
                    $matched[1][] = $firstValueType;
                    break;
                }
            }
        }

        return $all === $matched;
    }

    private static function isKeyedArrayContainedByArray(TKeyedArray $type, Atomic $container): bool
    {
        if (! $container instanceof TArray) {
            return false;
        }

        $containerKeys  = $container->type_params[0]->getAtomicTypes();
        $containerTypes = $container->type_params[1]->getAtomicTypes();

        foreach ($type->properties as $key => $property) {
            if ($property->possibly_undefined) {
                continue;
            }

            $keyType = is_int($key) ? new TInt() : new TString();
            $matches = (bool) array_filter(
                $containerKeys,
                static fn (Atomic $containerKey): bool => self::isContainedBy($keyType, $containerKey)
            );
            if (! $matches) {
                return false;
            }

            foreach ($property->getAtomicTypes() as $propertyType) {
                $matches = (bool) array_filter(
                    $containerTypes,
                    static fn (Atomic $containerType): bool => self::isContainedBy($propertyType, $containerType)
                );

                if (! $matches) {
                    return false;
                }
            }
        }

        if ($type->fallback_params !== null) {
            return self::isContainedByArray(new TArray($type->fallback_params), $container);
        }

        return true;
    }

    private static function isContainedByKeyedArray(Atomic $type, Atomic $container): bool
    {
        if (! ($type instanceof TKeyedArray && $container instanceof TKeyedArray)) {
            return false;
        }

        $typeSealed      = $type->fallback_params === null;
        $containerSealed = $container->fallback_params === null;
        if (! $typeSealed && $containerSealed) {
            return false;
        }

        /** @var array<string, list<Union>> $sealedProperties */
        $sealedProperties = [];
        foreach ($type->properties as $key => $typeProperty) {
            $containerProperty = $container->properties[$key] ?? null;
            $possiblyUndefined = $typeProperty->possibly_undefined;

            if ($containerProperty === null && ! $possiblyUndefined && $containerSealed) {
                return false;
            }
            if ($containerProperty === null && ! $possiblyUndefined) {
                $keyType                      = is_int($key) ? 'int' : 'string';
                $sealedProperties[$keyType][] = $typeProperty;
                continue;
            }
            if ($containerProperty === null) {
                return false;
            }
            if ($possiblyUndefined && ! in_array(new TNull(), $containerProperty->getAtomicTypes())) {
                return false;
            }

            $all = $matched = [];

            foreach ($typeProperty->getAtomicTypes() as $firstType) {
                $all[$key][] = $firstType;
                foreach ($containerProperty->getAtomicTypes() as $containerType) {
                    if (self::isContainedBy($firstType, $containerType)) {
                        $matched[$key][] = $firstType;
                        break;
                    }
                }
            }

            if ($all !== $matched) {
                return false;
            }
        }

        if ($sealedProperties === []) {
            return true;
        }

        assert($container->fallback_params !== null);

        $combined = [];
        foreach ($sealedProperties as $key => $properties) {
            $combined[$key] = [];
            foreach ($properties as $property) {
                $combined[$key] = array_merge($combined[$key], $property->getAtomicTypes());
            }
        }

        foreach ($combined as $key => $types) {
            assert($types !== []);
            $check = new TArray([new Union([$key === 'int' ? new TInt() : new TString()]), new Union($types)]);
            if (! self::isContainedByArray($check, new TArray($container->fallback_params))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @psalm-assert-if-true TLiteralFloat|TLiteralInt|TLiteralString $type
     */
    private static function isLiteral(Atomic $type): bool
    {
        return $type instanceof TLiteralFloat
            || $type instanceof TLiteralInt
            || $type instanceof TLiteralString;
    }
}
