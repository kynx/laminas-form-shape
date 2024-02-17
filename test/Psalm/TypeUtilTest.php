<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Psalm;

use Kynx\Laminas\FormShape\Psalm\ConfigLoader;
use Kynx\Laminas\FormShape\Psalm\TypeUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TEmptyNumeric;
use Psalm\Type\Atomic\TFalse;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNonFalsyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumeric;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TResource;
use Psalm\Type\Atomic\TScalar;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Atomic\TTrue;
use Psalm\Type\Union;
use stdClass;

use function array_values;
use function current;
use function fopen;

#[CoversClass(TypeUtil::class)]
final class TypeUtilTest extends TestCase
{
    use ConfigLoaderTrait;

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetConfig();
    }

    #[DataProvider('filterProvider')]
    public function testFilter(Union $union, Union $filter, Union $expected): void
    {
        $actual = TypeUtil::filter($union, $filter);
        self::assertEquals($expected, $actual);
    }

    /**
     * @psalm-suppress InvalidArgument Union([]) is invalid, but we need to test it
     */
    public static function filterProvider(): array
    {
        ConfigLoader::load(500);

        return [
            'empty union'           => [
                new Union([]),
                new Union([new TString()]),
                new Union([]),
            ],
            'empty filter'          => [
                new Union([new TString()]),
                new Union([]),
                new Union([]),
            ],
            'exists'                => [
                new Union([new TFalse(), new TTrue()]),
                new Union([new TTrue()]),
                new Union([new TTrue()]),
            ],
            'does not exist'        => [
                new Union([new TBool(), new TString()]),
                new Union([new TInt()]),
                new Union([]),
            ],
            'multiple filter types' => [
                new Union([new TInt()]),
                new Union([new TInt(), new TFloat()]),
                new Union([new TInt()]),
            ],
        ];
    }

    #[DataProvider('narrowProvider')]
    public function testNarrow(Union $search, Union $replace, Union $expected): void
    {
        $actual = TypeUtil::narrow($search, $replace);
        self::assertEquals($expected, $actual);
    }

    public static function narrowProvider(): array
    {
        ConfigLoader::load();

        return [
            'uses replace type'                          => [
                new Union([new TString()]),
                new Union([new TNumericString()]),
                new Union([new TNumericString()]),
            ],
            'uses search type'                           => [
                new Union([new TNumericString()]),
                new Union([new TString()]),
                new Union([new TNumericString()]),
            ],
            'uses same type'                             => [
                new Union([new TNonEmptyString()]),
                new Union([new TNonEmptyString()]),
                new Union([new TNonEmptyString()]),
            ],
            'removes type'                               => [
                new Union([new TBool(), new TString()]),
                new Union([new TTrue()]),
                new Union([new TTrue()]),
            ],
            'replaces narrowest'                         => [
                new Union([new TString()]),
                new Union([new TNonEmptyString(), TypeUtil::getAtomicStringFromLiteral('a')]),
                new Union([TypeUtil::getAtomicStringFromLiteral('a')]),
            ],
            'replaces multiple'                          => [
                new Union([new TScalar()]),
                new Union([new TInt(), new TFloat()]),
                new Union([new TInt(), new TFloat()]),
            ],
            'replaces string types'                      => [
                new Union([new TString()]),
                new Union([new TNonEmptyString(), new TNumericString()]),
                new Union([new TNumericString()]),
            ],
            'replaces string types order independent'    => [
                new Union([new TString()]),
                new Union([new TNumericString(), new TNonEmptyString(), new TString()]),
                new Union([new TNumericString()]),
            ],
            'replaces multiple literals'                 => [
                new Union([new TInt()]),
                new Union([new TLiteralInt(0), new TLiteralInt(1)]),
                new Union([new TLiteralInt(0), new TLiteralInt(1)]),
            ],
            'replaces multiple int ranges'               => [
                new Union([new TInt()]),
                new Union([new TIntRange(null, -1), new TIntRange(1, null)]),
                new Union([new TIntRange(null, -1), new TIntRange(1, null)]),
            ],
            'replaces string literals order independent' => [
                new Union([new TString()]),
                new Union([TypeUtil::getAtomicStringFromLiteral('a'), new TNonEmptyString()]),
                new Union([TypeUtil::getAtomicStringFromLiteral('a')]),
            ],
        ];
    }

    #[DataProvider('widenProvider')]
    public function testWiden(Union $search, Union $replace, Union $expected): void
    {
        $actual = TypeUtil::widen($search, $replace);
        self::assertEquals($expected, $actual);
    }

    public static function widenProvider(): array
    {
        return [
            'uses replace type' => [
                new Union([new TNonEmptyString()]),
                new Union([new TString()]),
                new Union([new TString()]),
            ],
            'uses search type'  => [
                new Union([new TString()]),
                new Union([new TNonEmptyString()]),
                new Union([new TString()]),
            ],
            'uses widest type'  => [
                new Union([new TNumericString()]),
                new Union([new TScalar(), new TString()]),
                new Union([new TScalar()]),
            ],
            'adds type'         => [
                new Union([new TInt()]),
                new Union([new TString()]),
                new Union([new TInt(), new TString()]),
            ],
        ];
    }

    #[DataProvider('replaceTypeProvider')]
    public function testReplaceType(Union $union, Atomic $search, Union $replace, Union $expected): void
    {
        $actual = TypeUtil::replaceType($union, $search, $replace);
        self::assertEquals($expected, $actual);
    }

    public static function replaceTypeProvider(): array
    {
        return [
            'does not exist' => [
                new Union([new TString()]),
                new TInt(),
                new Union([new TFloat()]),
                new Union([new TString()]),
            ],
            'exists'         => [
                new Union([new TString()]),
                new TString(),
                new Union([new TNonEmptyString()]),
                new Union([new TNonEmptyString()]),
            ],
        ];
    }

    #[DataProvider('toLooseUnionProvider')]
    public function testToLooseUnion(mixed $value, array $expected): void
    {
        $union  = TypeUtil::toLooseUnion($value);
        $actual = array_values($union->getAtomicTypes());
        self::assertEquals($expected, $actual);
    }

    public static function toLooseUnionProvider(): array
    {
        ConfigLoader::load(500);

        $resource = fopen('php://memory', 'r');
        return [
            'array'            => [
                ['a' => 123],
                [
                    new TKeyedArray([
                        'a' => new Union([new TLiteralInt(123), TypeUtil::getAtomicStringFromLiteral('123')]),
                    ]),
                ],
            ],
            'empty array'      => [
                [],
                [new TArray([Type::getArrayKey(), Type::getMixed()])],
            ],
            'list'             => [
                ['a', 'b'],
                [
                    Type::getNonEmptyListAtomic(new Union([
                        TypeUtil::getAtomicStringFromLiteral('a'),
                        TypeUtil::getAtomicStringFromLiteral('b'),
                    ])),
                ],
            ],
            'false'            => [
                false,
                [TypeUtil::getAtomicStringFromLiteral(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'true'             => [
                true,
                [new TFloat(), new TIntRange(null, -1), new TIntRange(1, null), new TNonEmptyString(), new TTrue()],
            ],
            'zero float'       => [
                0.0,
                [TypeUtil::getAtomicStringFromLiteral(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'float'            => [
                1.23,
                [new TLiteralFloat(1.23), TypeUtil::getAtomicStringFromLiteral('1.23')],
            ],
            'int float'        => [
                1.0,
                [new TLiteralFloat(1.0), TypeUtil::getAtomicStringFromLiteral('1'), new TLiteralInt(1)],
            ],
            'zero int'         => [
                0,
                [TypeUtil::getAtomicStringFromLiteral(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'int'              => [
                123,
                [new TLiteralInt(123), TypeUtil::getAtomicStringFromLiteral('123')],
            ],
            'null'             => [
                null,
                [TypeUtil::getAtomicStringFromLiteral(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'object'           => [
                new stdClass(),
                [new TNamedObject(stdClass::class)],
            ],
            'resource'         => [
                $resource,
                [new TResource(), new TNumeric()],
            ],
            'empty string'     => [
                '',
                [TypeUtil::getAtomicStringFromLiteral(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'int string'       => [
                '123',
                [TypeUtil::getAtomicStringFromLiteral('123'), new TLiteralInt(123)],
            ],
            'non-empty-string' => [
                'abc',
                [TypeUtil::getAtomicStringFromLiteral('abc')],
            ],
        ];
    }

    public function testToLooseUnionWithLongLiteralStringReturnsNonFalsyString(): void
    {
        $expected = [new TNonFalsyString()];
        ConfigLoader::load(1);
        $union  = TypeUtil::toLooseUnion('abc');
        $actual = array_values($union->getAtomicTypes());
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('toStrictUnionProvider')]
    public function testToStrictUnion(mixed $value, Atomic $expected): void
    {
        $union = TypeUtil::toStrictUnion($value);
        $types = array_values($union->getAtomicTypes());
        self::assertCount(1, $types);
        $actual = current($types);
        self::assertEquals($expected, $actual);
    }

    public static function toStrictUnionProvider(): array
    {
        ConfigLoader::load(200);

        $resource = fopen('php://memory', 'r');
        return [
            'empty array' => [
                [],
                new TArray([Type::getArrayKey(), new Union([new TMixed()])]),
            ],
            'list'        => [
                [1, 2],
                Type::getNonEmptyListAtomic(new Union([new TLiteralInt(1), new TLiteralInt(2)])),
            ],
            'array'       => [
                ['a' => true],
                new TKeyedArray(['a' => new Union([new TTrue()])]),
            ],
            'false'       => [
                false,
                new TFalse(),
            ],
            'true'        => [
                true,
                new TTrue(),
            ],
            'float'       => [
                1.23,
                new TLiteralFloat(1.23),
            ],
            'int'         => [
                123,
                new TLiteralInt(123),
            ],
            'null'        => [
                null,
                new TNull(),
            ],
            'object'      => [
                new stdClass(),
                new TNamedObject(stdClass::class),
            ],
            'resource'    => [
                $resource,
                new TResource(),
            ],
            'string'      => [
                'abc',
                TypeUtil::getAtomicStringFromLiteral('abc'),
            ],
        ];
    }
}
