<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Type;

use ArrayIterator;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\ClassString;
use Kynx\Laminas\FormCli\ArrayShape\Type\Generic;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function fopen;

/**
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
#[CoversClass(PsalmType::class)]
final class PsalmTypeTest extends TestCase
{
    #[DataProvider('phpTypeProvider')]
    public function testFromPhpType(mixed $value, PsalmType|ClassString $expected): void
    {
        $actual = PsalmType::fromPhpValue($value);
        self::assertEquals($expected, $actual);
    }

    public static function phpTypeProvider(): array
    {
        return [
            'array'    => [['a'], PsalmType::Array],
            'bool'     => [false, PsalmType::Bool],
            'float'    => [1.23, PsalmType::Float],
            'int'      => [123, PsalmType::Int],
            'iterable' => [new ArrayIterator([]), PsalmType::Iterable],
            'null'     => [null, PsalmType::Null],
            'object'   => [new stdClass(), new ClassString(stdClass::class)],
            'numeric'  => ['123', PsalmType::NumericString],
            'string'   => ['abc', PsalmType::String],
            'mixed'    => [fopen('php://memory', 'r'), PsalmType::Mixed],
        ];
    }

    /**
     * @param ParsedArray $types
     */
    #[DataProvider('arrayTypeProvider')]
    public function testHasArrayType(array $types, bool $expected): void
    {
        $actual = PsalmType::hasArrayType($types);
        self::assertSame($expected, $actual);
    }

    public static function arrayTypeProvider(): array
    {
        return [
            'empty'           => [[], false],
            'string'          => [[PsalmType::String], false],
            'array'           => [[PsalmType::Array], true],
            'non-empty-array' => [[PsalmType::NonEmptyArray], true],
            'generic string'  => [[new Generic(PsalmType::String, [])], false],
            'generic array'   => [[new Generic(PsalmType::Array, [])], true],
            'class'           => [[new ClassString(self::class)], false],
        ];
    }

    /**
     * @param ParsedArray $types
     */
    #[DataProvider('boolTypeProvider')]
    public function testHasBoolType(array $types, bool $expected): void
    {
        $actual = PsalmType::hasBoolType($types);
        self::assertSame($expected, $actual);
    }

    public static function boolTypeProvider(): array
    {
        return [
            'empty'          => [[], false],
            'string'         => [[PsalmType::String], false],
            'bool'           => [[PsalmType::Bool], true],
            'true'           => [[PsalmType::True], true],
            'false'          => [[PsalmType::False], true],
            'generic string' => [
                [
                    new Generic(
                        PsalmType::String,
                        []
                    ),
                ],
                false,
            ],
            'generic bool'   => [[new Generic(PsalmType::Bool, [])], true],
            'class'          => [[new ClassString(self::class)], false],
        ];
    }

    /**
     * @param ParsedArray $types
     */
    #[DataProvider('intTypeProvider')]
    public function testHasIntType(array $types, bool $expected): void
    {
        $actual = PsalmType::hasIntType($types);
        self::assertSame($expected, $actual);
    }

    public static function intTypeProvider(): array
    {
        return [
            'empty'          => [[], false],
            'string'         => [[PsalmType::String], false],
            'int'            => [[PsalmType::Int], true],
            'generic string' => [[new Generic(PsalmType::String, [])], false],
            'generic int'    => [[new Generic(PsalmType::Int, [])], true],
            'class'          => [[new ClassString(self::class)], false],
        ];
    }

    /**
     * @param ParsedArray $types
     */
    #[DataProvider('stringTypeProvider')]
    public function testHasStringType(array $types, bool $expected): void
    {
        $actual = PsalmType::hasStringType($types);
        self::assertSame($expected, $actual);
    }

    public static function stringTypeProvider(): array
    {
        return [
            'empty'          => [[], false],
            'string'         => [[PsalmType::String], true],
            'int'            => [[PsalmType::Int], false],
            'generic string' => [[new Generic(PsalmType::String, [])], true],
            'generic int'    => [[new Generic(PsalmType::Int, [])], false],
            'class'          => [[new ClassString(self::class)], false],
        ];
    }

    /**
     * @param ParsedArray $types
     */
    #[DataProvider('hasTypeProvider')]
    public function testHasType(AbstractParsedType|PsalmType $type, array $types, bool $expected): void
    {
        $actual = PsalmType::hasType($type, $types);
        self::assertSame($expected, $actual);
    }

    public static function hasTypeProvider(): array
    {
        return [
            'empty'    => [PsalmType::Bool, [], false],
            'array'    => [PsalmType::Array, [PsalmType::Array], true],
            'bool'     => [PsalmType::Bool, [PsalmType::Bool], true],
            'int'      => [PsalmType::Int, [PsalmType::Int], true],
            'string'   => [PsalmType::String, [PsalmType::String], true],
            'float'    => [PsalmType::Float, [PsalmType::Float], true],
            'no float' => [PsalmType::Float, [PsalmType::String], false],
        ];
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $expected
     */
    #[DataProvider('removeArrayTypesProvider')]
    public function testRemoveArrayTypes(array $types, array $expected): void
    {
        $actual = PsalmType::removeArrayTypes($types);
        self::assertEquals($expected, $actual);
    }

    public static function removeArrayTypesProvider(): array
    {
        return [
            'empty'           => [[], []],
            'none'            => [[PsalmType::String], [PsalmType::String]],
            'array'           => [[PsalmType::String, PsalmType::Array], [PsalmType::String]],
            'non-empty-array' => [[PsalmType::NonEmptyArray], []],
            'generic array'   => [[new Generic(PsalmType::Array, [])], []],
        ];
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $replacements
     * @param ParsedArray $expected
     */
    #[DataProvider('replaceArrayTypesProvider')]
    public function testReplaceArrayTypes(array $types, array $replacements, array $expected): void
    {
        $actual = PsalmType::replaceArrayTypes($types, $replacements);
        self::assertEquals($expected, $actual);
    }

    public static function replaceArrayTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'empty'         => [[], [PsalmType::NonEmptyArray], []],
            'none'          => [[PsalmType::String], [PsalmType::NonEmptyArray], [PsalmType::String]],
            'array'         => [[PsalmType::String, PsalmType::Array], [PsalmType::NonEmptyArray], [PsalmType::String, PsalmType::NonEmptyArray]],
            'generic array' => [[new Generic(PsalmType::Array, [])], [PsalmType::NonEmptyArray], [PsalmType::NonEmptyArray]],
        ];
        // phpcs:enable
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $replacements
     * @param ParsedArray $expected
     */
    #[DataProvider('replaceBoolTypesProvider')]
    public function testReplaceBoolTypes(array $types, array $replacements, array $expected): void
    {
        $actual = PsalmType::replaceBoolTypes($types, $replacements);
        self::assertEquals($expected, $actual);
    }

    public static function replaceBoolTypesProvider(): array
    {
        return [
            'empty' => [[], [PsalmType::NonEmptyArray], []],
            'none'  => [[PsalmType::String], [PsalmType::True], [PsalmType::String]],
            'bool'  => [[PsalmType::String, PsalmType::Bool], [PsalmType::True], [PsalmType::String, PsalmType::True]],
        ];
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $replacements
     * @param ParsedArray $expected
     */
    #[DataProvider('replaceIntTypesProvider')]
    public function testReplaceIntTypes(array $types, array $replacements, array $expected): void
    {
        $actual = PsalmType::replaceIntTypes($types, $replacements);
        self::assertEquals($expected, $actual);
    }

    public static function replaceIntTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'empty' => [[], [PsalmType::NonEmptyArray], []],
            'none'  => [[PsalmType::String], [PsalmType::Int], [PsalmType::String]],
            'int'   => [[PsalmType::String, PsalmType::Int], [PsalmType::NegativeInt, PsalmType::PositiveInt], [PsalmType::String, PsalmType::NegativeInt, PsalmType::PositiveInt]],
        ];
        // phpcs:enable
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $replacements
     * @param ParsedArray $expected
     */
    #[DataProvider('replaceStringTypesProvider')]
    public function testReplaceStringTypes(array $types, array $replacements, array $expected): void
    {
        $actual = PsalmType::replaceStringTypes($types, $replacements);
        self::assertEquals($expected, $actual);
    }

    public static function replaceStringTypesProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'empty'  => [[], [PsalmType::NonEmptyArray], []],
            'none'   => [[PsalmType::Int], [PsalmType::String], [PsalmType::Int]],
            'string' => [[PsalmType::Int, PsalmType::String], [PsalmType::NonEmptyString], [PsalmType::Int, PsalmType::NonEmptyString]],
        ];
        // phpcs:enable
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $expected
     */
    #[DataProvider('removeObjectTypesProvider')]
    public function testRemoveObjectTypes(array $types, array $expected): void
    {
        $actual = PsalmType::removeObjectTypes($types);
        self::assertEquals($expected, $actual);
    }

    public static function removeObjectTypesProvider(): array
    {
        return [
            'empty'         => [[], []],
            'none'          => [[PsalmType::String], [PsalmType::String]],
            'object'        => [[PsalmType::String, PsalmType::Object], [PsalmType::String]],
            'class'         => [[new ClassString(self::class)], []],
            'class generic' => [[new Generic(new ClassString(self::class), [])], []],
        ];
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $expected
     */
    #[DataProvider('removeTypeProvider')]
    public function testRemoveType(PsalmType $type, array $types, array $expected): void
    {
        $actual = PsalmType::removeType($type, $types);
        self::assertSame($expected, $actual);
    }

    public static function removeTypeProvider(): array
    {
        return [
            'empty'    => [PsalmType::String, [], []],
            'array'    => [PsalmType::Array, [PsalmType::Array], []],
            'bool'     => [PsalmType::Bool, [PsalmType::Bool], []],
            'int'      => [PsalmType::Int, [PsalmType::Int], []],
            'string'   => [PsalmType::String, [PsalmType::String], []],
            'float'    => [PsalmType::Float, [PsalmType::Int, PsalmType::Float], [PsalmType::Int]],
            'no float' => [PsalmType::Float, [PsalmType::Int], [PsalmType::Int]],
        ];
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $expected
     */
    #[DataProvider('replaceTypeProvider')]
    public function testReplaceType(PsalmType $type, PsalmType $replacement, array $types, array $expected): void
    {
        $actual = PsalmType::replaceType($type, $replacement, $types);
        self::assertSame($expected, $actual);
    }

    public static function replaceTypeProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'empty'    => [PsalmType::String, PsalmType::NonEmptyString, [], []],
            'array'    => [PsalmType::Array, PsalmType::NonEmptyArray, [PsalmType::Array], [PsalmType::NonEmptyArray]],
            'bool'     => [PsalmType::Bool, PsalmType::True, [PsalmType::Bool], [PsalmType::True]],
            'int'      => [PsalmType::Int, PsalmType::PositiveInt, [PsalmType::Int], [PsalmType::PositiveInt]],
            'string'   => [PsalmType::String, PsalmType::NonEmptyString, [PsalmType::String], [PsalmType::NonEmptyString]],
            'float'    => [PsalmType::Float, PsalmType::Null, [PsalmType::Int, PsalmType::Float], [PsalmType::Int, PsalmType::Null]],
            'no float' => [PsalmType::Float, PsalmType::Null, [PsalmType::Int], [PsalmType::Int]],
        ];
        // phpcs;enable
    }

    /**
     * @param ParsedArray $types
     * @param ParsedArray $filter
     */
    #[DataProvider('filterProvider')]
    public function testFilter(array $types, array $filter, array $expected): void
    {
        $actual = PsalmType::filter($types, $filter);
        self::assertEquals($expected, $actual);
    }

    public static function filterProvider(): array
    {
        $classString = new ClassString(stdClass::class);
        $generic     = new Generic(PsalmType::Array, []);
        $literal     = new Literal([123]);

        return [
            'empty types'    => [[], [PsalmType::String], []],
            'empty filter'   => [[PsalmType::String], [], []],
            'exists'         => [[PsalmType::Bool, PsalmType::String], [PsalmType::Bool], [PsalmType::Bool]],
            'does not exist' => [[PsalmType::Bool, PsalmType::String], [PsalmType::Int], []],
            'class string'   => [[$classString, PsalmType::String], [$classString], [$classString]],
            'generic'        => [[$generic, PsalmType::String], [PsalmType::Array], [$generic]],
            'literal'        => [[$literal, PsalmType::String], [PsalmType::Int], [$literal]],
        ];
    }

    public function testGetTypeStringReturnsType(): void
    {
        $expected = 'non-empty-array';
        $actual   = PsalmType::NonEmptyArray->getTypeString();
        self::assertSame($expected, $actual);
    }
}
