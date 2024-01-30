<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Type;

use Kynx\Laminas\FormShape\Type\Literal;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(Literal::class)]
final class LiteralTest extends TestCase
{
    public function testGetTypeStringSortsValues(): void
    {
        $expected = "'abc'|'zebedee'|123";
        $literal  = new Literal([123, "zebedee", 'abc']);
        $actual   = $literal->getTypeString();
        self::assertSame($expected, $actual);
    }

    /**
     * @param list<int|string> $values
     * @param VisitedArray $types
     */
    #[DataProvider('hasTypesProvider')]
    public function testHasTypes(array $values, array $types, bool $expected): void
    {
        $literal = new Literal($values);
        $actual  = $literal->matches($types);
        self::assertSame($expected, $actual);
    }

    public static function hasTypesProvider(): array
    {
        return [
            'empty values' => [[], [PsalmType::String], false],
            'empty search' => [['foo'], [], false],
            'has int'      => [[123], [PsalmType::Int], true],
            'no int'       => [['foo'], [PsalmType::Int], false],
            'has string'   => [['foo'], [PsalmType::String], true],
            'no string'    => [[123], [PsalmType::String], false],
            'other type'   => [[123, 'foo'], [PsalmType::Float], false],
        ];
    }

    /**
     * @param list<int|string> $values
     * @param VisitedArray $types
     * @param list<int|string> $expected
     */
    #[DataProvider('withTypesProvider')]
    public function testWithTypes(array $values, array $types, array $expected): void
    {
        $expected = new Literal($expected);
        $literal  = new Literal($values);
        $actual   = $literal->withTypes($types);
        self::assertEquals($expected, $actual);
    }

    public static function withTypesProvider(): array
    {
        return [
            'empty values' => [[], [PsalmType::String], []],
            'empty types'  => [['foo'], [], []],
            'strings'      => [['foo', 123, 'bar'], [PsalmType::String], ['foo', 'bar']],
            'ints'         => [[123, 'foo', 456], [PsalmType::Int], [123, 456]],
        ];
    }
}