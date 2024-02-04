<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Psalm\TypeComparator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TArrayKey;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNull;
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
            'mixed'                                       => [new TString(), new TMixed()],
            'inherited'                                   => [new TTrue(), new TBool()],
            'int array key'                               => [new TInt(), new TArrayKey()],
            'string array key'                            => [new TString(), new TArrayKey()],
            'bool scalar'                                 => [new TBool(), new TScalar()],
            'int scalar'                                  => [new TInt(), new TScalar()],
            'float scalar'                                => [new TFloat(), new TScalar()],
            'string scalar'                               => [new TString(), new TScalar()],
            'named object'                                => [
                new TNamedObject(stdClass::class),
                new TNamedObject(stdClass::class),
            ],
            'named object is instance of'                 => [
                new TNamedObject(self::class),
                new TNamedObject(TestCase::class),
            ],
            'identical array'                             => [
                new TArray([new Union([new TArrayKey()]), new Union([new TInt()])]),
                new TArray([new Union([new TArrayKey()]), new Union([new TInt()])]),
            ],
            'array key is contained'                      => [
                new TArray([new Union([new TInt()]), new Union([new TInt()])]),
                new TArray([new Union([new TArrayKey()]), new Union([new TInt()])]),
            ],
            'array value is contained'                    => [
                new TArray([new Union([new TArrayKey()]), new Union([new TInt()])]),
                new TArray([new Union([new TArrayKey()]), new Union([new TInt(), new TString()])]),
            ],
            'identical keyed array'                       => [
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                ]),
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                ]),
            ],
            'keyed array with fewer properties'           => [
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                ]),
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                    'b' => new Union([new TString()]),
                ]),
            ],
            'keyed array possibly undefined with null'    => [
                new TKeyedArray([
                    'a' => new Union([new TString()], ['possibly_undefined' => true]),
                ]),
                new TKeyedArray([
                    'a' => new Union([new TString(), new TNull()]),
                ]),
            ],
            'keyed array contained by possibly undefined' => [
                new TKeyedArray([
                    'a' => new Union([new TString()]),
                ]),
                new TKeyedArray([
                    'a' => new Union([new TString()], ['possibly_undefined' => true]),
                ]),
            ],
            'keyed array with contained fallback'         => [
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
            'not contained'                          => [new TString(), new TInt()],
            'named object with different class'      => [
                new TNamedObject(stdClass::class),
                new TNamedObject(self::class),
            ],
            'non-empty arrays with different counts' => [
                new TNonEmptyArray([new Union([new TInt()]), new Union([new TInt()])], 1),
                new TNonEmptyArray([new Union([new TInt()]), new Union([new TInt()])], 2),
            ],
            'array key not contained'                => [
                new TArray([new Union([new TInt()]), new Union([new TInt()])]),
                new TArray([new Union([new TString()]), new Union([new TInt()])]),
            ],
            'array value not contained'              => [
                new TArray([new Union([new TInt()]), new Union([new TInt()])]),
                new TArray([new Union([new TInt()]), new Union([new TString()])]),
            ],
            'keyed array not sealed'                 => [
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
            'keyed array with different keys'        => [
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
            'keyed array possibly undefined'         => [
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
            'keyed array with different values'      => [
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
            'keyed array with uncontained fallback'  => [
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
