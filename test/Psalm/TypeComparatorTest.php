<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Iterator;
use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Psalm\TypeComparator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFalse;
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
use Psalm\Type\Atomic\TScalar;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\Union;
use stdClass;

#[CoversClass(TypeComparator::class)]
final class TypeComparatorTest extends TestCase
{
    #[DataProvider('isContainedByTrueProvider')]
    public function testIsContainedReturnsTrue(Atomic $type, Atomic $container): void
    {
        $actual = TypeComparator::isContainedBy($type, $container);
        self::assertTrue($actual);
    }

    public static function isContainedByTrueProvider(): array
    {
        ConfigLoader::load(500);

        return [
            'string contained by mixed'                                        => [
                new TString(),
                new TMixed(),
            ],
            'literal float contained by literal float'                         => [
                new TLiteralFloat(1.23),
                new TLiteralFloat(1.23),
            ],
            'literal int contained by literal int'                             => [
                new TLiteralInt(123),
                new TLiteralInt(123),
            ],
            'literal string contained by literal string'                       => [
                TLiteralString::make('abc'),
                TLiteralString::make('abc'),
            ],
            'int range contained by int range'                                 => [
                new TIntRange(2, 3),
                new TIntRange(1, 4),
            ],
            'true contained by bool'                                           => [
                new TTrue(),
                new TBool(),
            ],
            'string contained by string'                                       => [
                new TString(),
                new TString(),
            ],
            'literal string contained by non empty string'                     => [
                TLiteralString::make('a'),
                new TNonEmptyString(),
            ],
            'literal string contained by numeric string'                       => [
                TLiteralString::make('123'),
                new TNumericString(),
            ],
            'numeric string contained by non empty string'                     => [
                new TNumericString(),
                new TNonEmptyString(),
            ],
            'non empty string contained by string'                             => [
                new TNonEmptyString(),
                new TString(),
            ],
            'int contained by array key'                                       => [
                new TInt(),
                new TArrayKey(),
            ],
            'string contained by array key'                                    => [
                new TString(),
                new TArrayKey(),
            ],
            'literal string contained by non empty scalar'                     => [
                TLiteralString::make('a'),
                new TNonEmptyScalar(),
            ],
            'negative int contained by non empty scalar'                       => [
                new TIntRange(-2, -1),
                new TNonEmptyScalar(),
            ],
            'positive int contained by non empty scalar'                       => [
                new TIntRange(1, 2),
                new TNonEmptyScalar(),
            ],
            'non empty string contained by non empty scalar'                   => [
                new TNonEmptyString(),
                new TNonEmptyScalar(),
            ],
            'true contained by non empty scalar'                               => [
                new TTrue(),
                new TNonEmptyScalar(),
            ],
            'bool contained by scalar'                                         => [
                new TBool(),
                new TScalar(),
            ],
            'int contained by scalar'                                          => [
                new TInt(),
                new TScalar(),
            ],
            'float contained by scalar'                                        => [
                new TFloat(),
                new TScalar(),
            ],
            'string contained by scalar'                                       => [
                new TString(),
                new TScalar(),
            ],
            'named object contained by named object'                           => [
                new TNamedObject(stdClass::class),
                new TNamedObject(stdClass::class),
            ],
            'named object contained by parent'                                 => [
                new TNamedObject(self::class),
                new TNamedObject(TestCase::class),
            ],
            'generic object contained by generic object'                       => [
                new TGenericObject(Iterator::class, [new Union([new TString()])]),
                new TGenericObject(Iterator::class, [new Union([new TInt(), new TString()])]),
            ],
            'array contained by array'                                         => [
                new TArray([new Union([new TArrayKey()]), new Union([new TInt()])]),
                new TArray([new Union([new TArrayKey()]), new Union([new TInt()])]),
            ],
            'non empty array contained by array'                               => [
                new TNonEmptyArray([Type::getArrayKey(), Type::getMixed()]),
                new TArray([Type::getArrayKey(), Type::getMixed()]),
            ],
            'array with int key contained by array key'                        => [
                new TArray([new Union([new TInt()]), new Union([new TInt()])]),
                new TArray([new Union([new TArrayKey()]), new Union([new TInt()])]),
            ],
            'array with in value contained by array with int value'            => [
                new TArray([new Union([new TArrayKey()]), new Union([new TInt()])]),
                new TArray([new Union([new TArrayKey()]), new Union([new TInt(), new TString()])]),
            ],
            'keyed array contained by keyed array'                             => [
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                ]),
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                ]),
            ],
            'keyed array with one property contained by keyed array with more' => [
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                ]),
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                    'b' => new Union([new TString()]),
                ]),
            ],
            'keyed array with possibly undefined property contained by keyed array with null property' => [
                new TKeyedArray([
                    'a' => new Union([new TString()], ['possibly_undefined' => true]),
                ]),
                new TKeyedArray([
                    'a' => new Union([new TString(), new TNull()]),
                ]),
            ],
            'keyed array contained by keyed array with possibly undefinedproperty '                    => [
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                ]),
                new TKeyedArray([
                    'a' => new Union([new TString()], ['possibly_undefined' => true]),
                ]),
            ],
            'keyed array contained by keyed array with fallback'                                       => [
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                        'b' => new Union([new TString()]),
                    ],
                ),
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                    ],
                    fallback_params: [
                        new Union([new TArrayKey()]),
                        new Union([new TString()]),
                    ],
                ),
            ],
        ];
    }

    #[DataProvider('isContainedByFalseProvider')]
    public function testIsContainedReturnsFalse(Atomic $type, Atomic $container): void
    {
        $actual = TypeComparator::isContainedBy($type, $container);
        self::assertFalse($actual);
    }

    public static function isContainedByFalseProvider(): array
    {
        ConfigLoader::load(500);

        return [
            'not contained'                                               => [
                new TString(),
                new TInt(),
            ],
            'float contained by literal float'                            => [
                new TFloat(),
                new TLiteralFloat(1.23),
            ],
            'int contained by literal float'                              => [
                new TInt(),
                new TLiteralFloat(1.23),
            ],
            'int contained by literal int'                                => [
                new TInt(),
                new TLiteralInt(123),
            ],
            'string contained by literal string'                          => [
                new TString(),
                TLiteralString::make('abc'),
            ],
            'int range contained by min bound'                            => [
                new TIntRange(1, 4),
                new TIntRange(2, null),
            ],
            'int range contained by max bound'                            => [
                new TIntRange(1, 4),
                new TIntRange(null, 3),
            ],
            'empty literal string contained by non empty string'          => [
                TLiteralString::make(''),
                new TNonEmptyString(),
            ],
            'alpha literal string contained by numeric string'            => [
                TLiteralString::make('abc'),
                new TNumericString(),
            ],
            'string contained by non empty string'                        => [
                new TString(),
                new TNonEmptyString(),
            ],
            'string contained by numeric string'                          => [
                new TString(),
                new TNumericString(),
            ],
            'empty literal string contained by non empty scalar'          => [
                TLiteralString::make(''),
                new TNonEmptyScalar(),
            ],
            'int range contained by non empty scalar'                     => [
                new TIntRange(-1, 1),
                new TNonEmptyScalar(),
            ],
            'false contained by non empty scalar'                         => [
                new TFalse(),
                new TNonEmptyScalar(),
            ],
            'named object contained by non empty scalar'                  => [
                new TNamedObject(stdClass::class),
                new TNonEmptyScalar(),
            ],
            'named object contained by scalar'                            => [
                new TNamedObject(stdClass::class),
                new TScalar(),
            ],
            'named object contained by generic object'                    => [
                new TNamedObject(Iterator::class),
                new TGenericObject(Iterator::class, [new Union([new TInt()])]),
            ],
            'named object contained by named object with different class' => [
                new TNamedObject(stdClass::class),
                new TNamedObject(self::class),
            ],
            'generic object contained by more specific generic object'    => [
                new TGenericObject(Iterator::class, [new Union([new TInt(), new TString()])]),
                new TGenericObject(Iterator::class, [new Union([new TInt()])]),
            ],
            'array contained by non empty array'                          => [
                new TArray([Type::getArrayKey(), Type::getMixed()]),
                new TNonEmptyArray([Type::getArrayKey(), Type::getMixed()]),
            ],
            'non-empty array contained by non empty array with different counts'    => [
                new TNonEmptyArray([new Union([new TInt()]), new Union([new TInt()])], 1),
                new TNonEmptyArray([new Union([new TInt()]), new Union([new TInt()])], 2),
            ],
            'array with int key contained by array with string key'                 => [
                new TArray([new Union([new TInt()]), new Union([new TInt()])]),
                new TArray([new Union([new TString()]), new Union([new TInt()])]),
            ],
            'array with int value contained by array with string value'             => [
                new TArray([new Union([new TInt()]), new Union([new TInt()])]),
                new TArray([new Union([new TInt()]), new Union([new TString()])]),
            ],
            'keyed array not sealed contained by sealed keyed array'                => [
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                    ],
                    fallback_params: [
                        new Union([new TArrayKey()]),
                        new Union([new TString()]),
                    ]
                ),
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                    ],
                ),
            ],
            'keyed array contained by keyed array with different keys'              => [
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                    ]
                ),
                new TKeyedArray(
                    properties: [
                        'b' => new Union([new TString()]),
                    ]
                ),
            ],
            'keyed array possibly undefined contained by keyed array non undefined' => [
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()], ['possibly_undefined' => true]),
                    ]
                ),
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                    ]
                ),
            ],
            'keyed array contained by keyed array with different values'            => [
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                    ]
                ),
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TInt()]),
                    ]
                ),
            ],
            'keyed array contained by keyed array with uncontained fallback'        => [
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                        'b' => new Union([new TString()]),
                    ],
                ),
                new TKeyedArray(
                    properties: [
                        'a' => new Union([new TString()]),
                    ],
                    fallback_params: [
                        new Union([new TArrayKey()]),
                        new Union([new TInt()]),
                    ]
                ),
            ],
        ];
    }

    /**
     * @psalm-suppress InvalidArgument Yes, yes, we're testing whether it's invalid
     */
    public function testIsContainedByArrayInvalidParamsReturnsFalse(): void
    {
        $type      = new TArray([new Union([new TInt()])]);
        $container = new TArray([new Union([new TInt()]), new Union([new TInt()])]);
        $actual    = TypeComparator::isContainedBy($type, $container);
        self::assertFalse($actual);
    }
}
