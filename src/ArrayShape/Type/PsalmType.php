<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

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
 * @see \Psalm\Internal\Type\TypeTokenizer for a full list
 *
 * @psalm-import-type ParsedUnion from AbstractParsedType
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
enum PsalmType: string implements TypeStringInterface
{
    // phpcs:disable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFallbackGlobalName
    case Int             = 'int';
    case String          = 'string';
    case Float           = 'float';
    case Bool            = 'bool';
    case False           = 'false';
    case True            = 'true';
    case Object          = 'object';
    case Empty           = 'empty';
    case Array           = 'array';
    case NonEmptyArray   = 'non-empty-array';
    case NonEmptyString  = 'non-empty-string';
    case Iterable        = 'iterable';
    case Null            = 'null';
    case NumericString   = 'numeric-string';
    case LowercaseString = 'lowercase-string';
    case PositiveInt     = 'positive-int';
    case NegativeInt     = 'negative-int';
    case ClassString     = 'class-string';
    case Mixed           = 'mixed';
    // phpcs:enable

    private const ARRAY_TYPES = [
        self::Array,
        self::NonEmptyArray,
    ];

    private const BOOL_TYPES = [
        self::Bool,
        self::True,
        self::False,
    ];

    private const INT_TYPES = [
        self::Int,
        self::PositiveInt,
        self::NegativeInt,
    ];

    private const STRING_TYPES = [
        self::String,
        self::NumericString,
        self::NonEmptyString,
    ];

    public static function fromPhpValue(mixed $value): self|ClassString
    {
        return match (true) {
            is_array($value)    => self::Array,
            is_bool($value)     => self::Bool,
            is_float($value)    => self::Float,
            is_int($value)      => self::Int,
            is_iterable($value) => self::Iterable,
            $value === null     => self::Null,
            is_object($value)   => new ClassString($value::class),
            is_numeric($value)  => self::NumericString,
            is_string($value)   => self::String,
            default             => self::Mixed
        };
    }

    /**
     * @param ParsedArray $types
     */
    public static function hasArrayType(array $types): bool
    {
        return self::getTypes(self::ARRAY_TYPES, $types) !== [];
    }

    /**
     * @param ParsedArray $types
     */
    public static function hasBoolType(array $types): bool
    {
        return self::getTypes(self::BOOL_TYPES, $types) !== [];
    }

    /**
     * @param ParsedArray $types
     */
    public static function hasIntType(array $types): bool
    {
        return self::getTypes(self::INT_TYPES, $types) !== [];
    }

    /**
     * @param ParsedArray $types
     */
    public static function hasStringType(array $types): bool
    {
        return self::getTypes(self::STRING_TYPES, $types) !== [];
    }

    /**
     * @param ParsedUnion $type
     * @param ParsedArray $types
     */
    public static function hasType(AbstractParsedType|PsalmType $type, array $types): bool
    {
        return match ($type) {
            PsalmType::Array  => self::hasArrayType($types),
            PsalmType::Bool   => self::hasBoolType($types),
            PsalmType::Int    => self::hasIntType($types),
            PsalmType::String => self::hasStringType($types),
            default           => self::getTypes([$type], $types) !== [],
        };
    }

    /**
     * @param ParsedArray $types
     * @return ParsedArray
     */
    public static function removeArrayTypes(array $types): array
    {
        return self::removeTypes($types, self::getTypes(self::ARRAY_TYPES, $types));
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $replacements
     * @return ParsedArray
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
     * @param ParsedArray $types
     * @return ParsedArray
     */
    public static function removeBoolTypes(array $types): array
    {
        return self::removeTypes($types, self::getTypes(self::BOOL_TYPES, $types));
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $replacements
     * @return ParsedArray
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
     * @param ParsedArray $types
     * @return ParsedArray
     */
    public static function removeIntTypes(array $types): array
    {
        return self::removeTypes($types, self::getTypes(self::INT_TYPES, $types));
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $replacements
     * @return ParsedArray
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
     * @param ParsedArray $types
     * @return ParsedArray
     */
    public static function removeStringTypes(array $types): array
    {
        return self::removeTypes($types, self::getTypes(self::STRING_TYPES, $types));
    }

    /**
     * @param ParsedArray $types
     * @return ParsedArray
     */
    public static function removeType(PsalmType $type, array $types): array
    {
        return match ($type) {
            PsalmType::Array  => self::removeArrayTypes($types),
            PsalmType::Bool   => self::removeBoolTypes($types),
            PsalmType::Int    => self::removeIntTypes($types),
            PsalmType::Object => self::removeObjectTypes($types),
            PsalmType::String => self::removeStringTypes($types),
            default           => array_filter(
                $types,
                static fn (AbstractParsedType|PsalmType $test): bool => $test !== $type
            ),
        };
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $replacements
     * @return ParsedArray
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
     * @param ParsedArray $types
     * @return ParsedArray
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
            if ($type === self::Object) {
                $remove[] = $type;
            }
        }

        return self::removeTypes($types, $remove);
    }

    /**
     * @param ParsedArray $types
     * @return ParsedArray
     */
    public static function replaceType(PsalmType $type, PsalmType $replacement, array $types): array
    {
        return match ($type) {
            PsalmType::Array  => self::replaceArrayTypes($types, [$replacement]),
            PsalmType::Bool   => self::replaceBoolTypes($types, [$replacement]),
            PsalmType::Int    => self::replaceIntTypes($types, [$replacement]),
            PsalmType::String => self::replaceStringTypes($types, [$replacement]),
            default           => self::hasType($type, $types)
                ? array_merge(self::removeType($type, $types), [$replacement])
                : $types,
        };
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $filter
     * @return ParsedArray
     */
    public static function filter(array $types, array $filter): array
    {
        return self::getTypes($filter, $types);
    }

    public function getTypeString(string $indent = '    '): string
    {
        return $this->value;
    }

    /**
     * @param ParsedArray $search
     * @param ParsedArray $types
     * @return ParsedArray
     */
    private static function getTypes(array $search, array $types): array
    {
        $found = [];
        foreach ($types as $type) {
            $found[] = match ($type::class) {
                ClassString::class, PsalmType::class => in_array($type, $search) ? $type : null,
                Generic::class                       => in_array($type->type, $search) ? $type : null,
                Literal::class                       => $type->hasTypes($search) ? $type->withTypes($search) : null
            };
        }

        return array_filter($found);
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $remove
     * @return ParsedArray
     */
    private static function removeTypes(array $types, array $remove): array
    {
        return array_filter(
            $types,
            static fn (AbstractParsedType|PsalmType $type): bool => ! in_array($type, $remove)
        );
    }
}
