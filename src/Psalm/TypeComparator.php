<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Psalm;

use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TScalar;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

use function array_merge;
use function assert;
use function count;
use function in_array;
use function is_a;
use function is_int;

/**
 * @internal
 *
 * @psalm-internal \Kynx\Laminas\FormShape
 * @psalm-internal \KynxTest\Laminas\FormShape
 */
final readonly class TypeComparator
{
    /**
     * Returns true if `$first` type is contained by `$second` type
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
            $type instanceof TNamedObject
            || $type instanceof TArray
            || $type instanceof TKeyedArray
        ) {
            return false;
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

    private static function isContainedByScalar(Atomic $type, Atomic $container): bool
    {
        if (! $container instanceof TScalar) {
            return false;
        }

        return $type instanceof TBool
            || $type instanceof TInt
            || $type instanceof TFloat
            || $type instanceof TString;
    }

    private static function isContainedByNamedObject(Atomic $type, Atomic $oontainer): bool
    {
        if (! ($type instanceof TNamedObject && $oontainer instanceof TNamedObject)) {
            return false;
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        return is_a($type->value, $oontainer->value, true);
    }

    private static function isContainedByArray(Atomic $type, Atomic $container): bool
    {
        if (! ($type instanceof TArray && $container instanceof TArray)) {
            return false;
        }

        if (
            $type instanceof TNonEmptyArray
            && $container instanceof TNonEmptyArray
            && $type->count !== $container->count
        ) {
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
