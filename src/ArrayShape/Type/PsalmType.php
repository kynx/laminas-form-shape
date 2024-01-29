<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormCli\ArrayShape\Type;

/**
 * @see \Psalm\Internal\Type\TypeTokenizer for a full list
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

    public const ARRAY_TYPES = [
        self::Array,
        self::NonEmptyArray,
    ];

    public const BOOL_TYPES = [
        self::Bool,
        self::True,
        self::False,
    ];

    public const INT_TYPES = [
        self::Int,
        self::PositiveInt,
        self::NegativeInt,
    ];

    public const STRING_TYPES = [
        self::String,
        self::NumericString,
        self::NonEmptyString,
    ];

    public function getTypeString(int $indent = 0, string $indentString = '    '): string
    {
        return $this->value;
    }
}
