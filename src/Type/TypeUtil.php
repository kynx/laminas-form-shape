<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Type;

use function array_filter;
use function array_merge;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_iterable;
use function is_numeric;
use function is_object;
use function is_string;

/**
 * @psalm-type VisitedUnion = AbstractVisitedType|PsalmType
 * @psalm-type VisitedArray = array<array-key, VisitedUnion>
 */
final readonly class TypeUtil
{
    /**
     * @param VisitedArray $types
     * @param VisitedArray $filter
     * @return VisitedArray
     */
    public static function filter(array $types, array $filter): array
    {
        return self::getTypes($filter, $types);
    }

    public static function fromPhpValue(mixed $value): PsalmType|ClassString
    {
        return match (true) {
            is_array($value) => PsalmType::Array,
            is_bool($value) => PsalmType::Bool,
            is_float($value) => PsalmType::Float,
            is_int($value) => PsalmType::Int,
            is_iterable($value) => PsalmType::Iterable,
            $value === null => PsalmType::Null,
            is_object($value) => new ClassString($value::class),
            is_numeric($value) => PsalmType::NumericString,
            is_string($value) => PsalmType::String,
            default => PsalmType::Mixed
        };
    }

    /**
     * @param VisitedArray $types
     */
    public static function hasArrayType(array $types): bool
    {
        return self::getTypes(PsalmType::ARRAY_TYPES, $types) !== [];
    }

    /**
     * @param VisitedArray $types
     */
    public static function hasBoolType(array $types): bool
    {
        return self::getTypes(PsalmType::BOOL_TYPES, $types) !== [];
    }

    /**
     * @param VisitedArray $types
     */
    public static function hasIntType(array $types): bool
    {
        return self::getTypes(PsalmType::INT_TYPES, $types) !== [];
    }

    /**
     * @param VisitedArray $types
     */
    public static function hasStringType(array $types): bool
    {
        return self::getTypes(PsalmType::STRING_TYPES, $types) !== [];
    }

    /**
     * @param VisitedUnion $type
     * @param VisitedArray $types
     */
    public static function hasType(AbstractVisitedType|PsalmType $type, array $types): bool
    {
        return match ($type) {
            PsalmType::Array => self::hasArrayType($types),
            PsalmType::Bool => self::hasBoolType($types),
            PsalmType::Int => self::hasIntType($types),
            PsalmType::String => self::hasStringType($types),
            default => self::getTypes([$type], $types) !== [],
        };
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    public static function removeArrayTypes(array $types): array
    {
        return self::removeTypes($types, self::getTypes(PsalmType::ARRAY_TYPES, $types));
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    public static function removeBoolTypes(array $types): array
    {
        return self::removeTypes($types, self::getTypes(PsalmType::BOOL_TYPES, $types));
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    public static function removeIntTypes(array $types): array
    {
        return self::removeTypes($types, self::getTypes(PsalmType::INT_TYPES, $types));
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    public static function removeObjectTypes(array $types): array
    {
        $remove = [];
        foreach ($types as $type) {
            if ($type instanceof ClassString) {
                $remove[] = $type;
            }
            if ($type instanceof Generic && $type->type instanceof ClassString) {
                $remove[] = $type;
            }
            if ($type === PsalmType::Object) {
                $remove[] = $type;
            }
        }

        return self::removeTypes($types, $remove);
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    public static function removeStringTypes(array $types): array
    {
        return self::removeTypes($types, self::getTypes(PsalmType::STRING_TYPES, $types));
    }

    /**
     * @param VisitedArray $types
     * @param VisitedArray $remove
     * @return VisitedArray
     */
    public static function removeTypes(array $types, array $remove): array
    {
        return array_filter(
            $types,
            static fn(AbstractVisitedType|PsalmType $type): bool => ! in_array($type, $remove)
        );
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    public static function removeType(PsalmType $type, array $types): array
    {
        return match ($type) {
            PsalmType::Array => self::removeArrayTypes($types),
            PsalmType::Bool => self::removeBoolTypes($types),
            PsalmType::Int => self::removeIntTypes($types),
            PsalmType::Object => self::removeObjectTypes($types),
            PsalmType::String => self::removeStringTypes($types),
            default => array_filter(
                $types,
                static fn(AbstractVisitedType|PsalmType $test): bool => $test !== $type
            ),
        };
    }

    /**
     * @param VisitedArray $types
     * @param VisitedArray $replacements
     * @return VisitedArray
     */
    public static function replaceArrayTypes(array $types, array $replacements): array
    {
        if (! self::hasArrayType($types)) {
            return $types;
        }

        $types = self::removeArrayTypes($types);
        return array_merge($types, $replacements);
    }

    /**
     * @param VisitedArray $types
     * @param VisitedArray $replacements
     * @return VisitedArray
     */
    public static function replaceBoolTypes(array $types, array $replacements): array
    {
        if (! self::hasBoolType($types)) {
            return $types;
        }

        $types = self::removeBoolTypes($types);
        return array_merge($types, $replacements);
    }

    /**
     * @param VisitedArray $types
     * @param VisitedArray $replacements
     * @return VisitedArray
     */
    public static function replaceIntTypes(array $types, array $replacements): array
    {
        if (! self::hasIntType($types)) {
            return $types;
        }

        $types = self::removeIntTypes($types);
        return array_merge($types, $replacements);
    }

    /**
     * @param VisitedArray $types
     * @param VisitedArray $replacements
     * @return VisitedArray
     */
    public static function replaceStringTypes(array $types, array $replacements): array
    {
        if (! self::hasStringType($types)) {
            return $types;
        }

        $types = self::removeStringTypes($types);
        return array_merge($types, $replacements);
    }

    /**
     * @param VisitedArray $types
     * @return VisitedArray
     */
    public static function replaceType(PsalmType $type, PsalmType $replacement, array $types): array
    {
        return match ($type) {
            PsalmType::Array => self::replaceArrayTypes($types, [$replacement]),
            PsalmType::Bool => self::replaceBoolTypes($types, [$replacement]),
            PsalmType::Int => self::replaceIntTypes($types, [$replacement]),
            PsalmType::String => self::replaceStringTypes($types, [$replacement]),
            default => self::hasType($type, $types)
                ? array_merge(self::removeType($type, $types), [$replacement])
                : $types,
        };
    }

    /**
     * @param VisitedArray $search
     * @param VisitedArray $types
     * @return VisitedArray
     */
    private static function getTypes(array $search, array $types): array
    {
        $found = [];
        foreach ($types as $type) {
            $found[] = match ($type::class) {
                ClassString::class => $type->matches($search) ? $type : null,
                Generic::class => in_array($type->type, $search) ? $type : null,
                Literal::class => $type->matches($search) ? $type->withTypes($search) : null,
                PsalmType::class => in_array($type, $search) ? $type : null,
            };
        }

        return array_filter($found);
    }
}
