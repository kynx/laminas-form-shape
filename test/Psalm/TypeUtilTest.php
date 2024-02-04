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
use stdClass;

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
    public function testReplace(Union $union, Atomic $search, Union $replace, Union $expected): void
    {
        $actual = TypeUtil::replace($union, $search, $replace);
        self::assertEquals($expected, $actual);
    }

    public static function replaceProvider(): array
    {
        ConfigLoader::load(500);

        return [
            'replaces'          => [
                new Union([new TInt(), new TString()]),
                new TString(),
                new Union([new TNumericString()]),
                new Union([new TInt(), new TNumericString()]),
            ],
            'replaces multiple' => [
                new Union([new TString()]),
                new TString(),
                new Union([new TFloat(), new TInt()]),
                new Union([new TFloat(), new TInt()]),
            ],
            'does not replace'  => [
                new Union([new TString(), new TNull()]),
                new TFloat(),
                new Union([new TInt()]),
                new Union([new TString(), new TNull()]),
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

    #[DataProvider('toTypeProvider')]
    public function testToType(mixed $value, Atomic $expected): void
    {
        $actual = TypeUtil::toType($value);
        self::assertEquals($expected, $actual);
    }

    public static function toTypeProvider(): array
    {
        ConfigLoader::load(500);

        $resource = fopen('php://memory', 'r');
        return [
            'array int key'    => [
                [1 => 'b'],
                new TNonEmptyArray([Type::getInt(), new Union([new TNonEmptyString()])]),
            ],
            'array mixed key'  => [
                ['a' => 'b', 1 => 'c'],
                new TNonEmptyArray([Type::getArrayKey(), new Union([new TNonEmptyString()])]),
            ],
            'array string key' => [
                ['a' => 'b'],
                new TNonEmptyArray([Type::getString(), new Union([new TNonEmptyString()])]),
            ],
            'empty array'      => [
                [],
                new TArray([Type::getArrayKey(), new Union([new TMixed()])]),
            ],
            'list'             => [
                ['a', 'b'],
                Type::getNonEmptyListAtomic(new Union([new TNonEmptyString()])),
            ],
            'empty string'     => ['', new TString()],
            'false'            => [false, new TFalse()],
            'float'            => [1.23, new TFloat()],
            'int'              => [123, new TInt()],
            'non-empty-string' => ['abc', new TNonEmptyString()],
            'null'             => [null, new TNull()],
            'numeric'          => ['123', new TNumericString()],
            'object'           => [new stdClass(), new TNamedObject(stdClass::class)],
            'resource'         => [$resource, new TResource()],
            'true'             => [true, new TTrue()],
        ];
    }

    #[DataProvider('toLiteralTypeProvider')]
    public function testToLiteralType(mixed $value, Atomic $expected): void
    {
        $actual = TypeUtil::toLiteralType($value);
        self::assertEquals($expected, $actual);
    }

    public static function toLiteralTypeProvider(): array
    {
        ConfigLoader::load(200);

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
                ['a' => 'b'],
                new TKeyedArray([
                    'a' => new Union([TLiteralString::make('b')]),
                ]),
            ],
            'float'       => [1.23, new TLiteralFloat(1.23)],
            'int'         => [123, new TLiteralInt(123)],
            'string'      => ['abc', TLiteralString::make('abc')],
            'other'       => [false, new TFalse()],
        ];
    }
}
