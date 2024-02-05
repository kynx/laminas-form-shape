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
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyScalar;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumeric;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TResource;
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

    #[DataProvider('replaceProvider')]
    public function testReplace(Union $search, Union $replace, bool $preserve, Union $expected): void
    {
        $actual = TypeUtil::narrow($search, $replace, $preserve);
        self::assertEquals($expected, $actual);
    }

    public static function replaceProvider(): array
    {
        ConfigLoader::load(500);

        return [
            'replace, no preserve'           => [
                new Union([new TInt(), new TString()]),
                new Union([new TNumericString()]),
                false,
                new Union([new TNumericString()]),
            ],
            'replaces multiple, no preserve' => [
                new Union([new TBool(), new TString()]),
                new Union([new TTrue(), new TNonEmptyString()]),
                false,
                new Union([new TTrue(), new TNonEmptyString()]),
            ],
            'replace, preserve'              => [
                new Union([new TString(), new TNull()]),
                new Union([new TNumericString()]),
                true,
                new Union([new TNumericString(), new TNull()]),
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

    #[DataProvider('hasTypeProvider')]
    public function testHasType(Union $union, Atomic $type, bool $expected): void
    {
        $actual = TypeUtil::hasType($union, $type);
        self::assertSame($expected, $actual);
    }

    public static function hasTypeProvider(): array
    {
        ConfigLoader::load(500);

        return [
            'does not exist' => [new Union([new TString()]), new TInt(), false],
            'exists'         => [new Union([new TString()]), new TString(), true],
        ];
    }

    #[DataProvider('toLaxUnion')]
    public function testToLaxUnion(mixed $value, array $expected): void
    {
        $union  = TypeUtil::toLaxUnion($value);
        $actual = array_values($union->getAtomicTypes());
        self::assertEquals($expected, $actual);
    }

    public static function toLaxUnion(): array
    {
        ConfigLoader::load(500);

        $resource = fopen('php://memory', 'r');
        return [
            'array'            => [
                ['a' => true],
                [new TKeyedArray(['a' => new Union([new TNonEmptyScalar()])])],
            ],
            'empty array'      => [
                [],
                [new TArray([Type::getArrayKey(), new Union([new TMixed()])])],
            ],
            'list'             => [
                ['a', 'b'],
                [Type::getNonEmptyListAtomic(new Union([TLiteralString::make('a'), TLiteralString::make('b')]))],
            ],
            'false'            => [
                false,
                [TLiteralString::make(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'true'             => [
                true,
                [new TNonEmptyScalar()],
            ],
            'zero float'       => [
                0.0,
                [TLiteralString::make(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'float'            => [
                1.23,
                [new TLiteralFloat(1.23), TLiteralString::make('1.23')],
            ],
            'int float'        => [
                1.0,
                [new TLiteralFloat(1.0), TLiteralString::make('1'), new TLiteralInt(1)],
            ],
            'zero int'         => [
                0,
                [TLiteralString::make(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'int'              => [
                123,
                [new TLiteralInt(123), TLiteralString::make('123')],
            ],
            'null'             => [
                null,
                [TLiteralString::make(''), new TFalse(), new TEmptyNumeric(), new TNull()],
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
                [TLiteralString::make(''), new TFalse(), new TEmptyNumeric(), new TNull()],
            ],
            'int string'       => [
                '123',
                [TLiteralString::make('123'), new TLiteralInt(123)],
            ],
            'non-empty-string' => [
                'abc',
                [TLiteralString::make('abc')],
            ],
        ];
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
                TLiteralString::make('abc'),
            ],
        ];
    }
}
