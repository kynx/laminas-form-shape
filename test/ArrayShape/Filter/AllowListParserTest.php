<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListParser;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractParsedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\Literal;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\AllowList;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Laminas\FormCli\ArrayShape\Filter\AllowListParser
 * @psalm-import-type ParsedArray from AbstractParsedType
 */
final class AllowListParserTest extends TestCase
{
    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypeLiteralProvider')]
    public function testGetTypesReturnsLiteral(FilterInterface $filter, array $existing, array $expected): void
    {
        $parser = new AllowListParser();
        $actual = $parser->getTypes($filter, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function getTypeLiteralProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'         => [new Boolean(), [PsalmType::Int], [PsalmType::Int]],
            'strict list'     => [new AllowList(['list' => ['foo', 123], 'strict' => true]), [PsalmType::Int, PsalmType::String, PsalmType::Null], [new Literal(["'foo'", 123]), PsalmType::Null]],
            'lax list'        => [new AllowList(['list' => ['foo', 123], 'strict' => false]), [PsalmType::Int, PsalmType::String, PsalmType::Null], [new Literal(["'foo'", 123, "'123'"]), PsalmType::Null]],
            'strict existing' => [new AllowList(['list' => ['foo', 123], 'strict' => true]), [PsalmType::Int], [new Literal([123])]],
            'lax existing'    => [new AllowList(['list' => ['foo', 123], 'strict' => false]), [PsalmType::String], [new Literal(["'foo'", "'123'"])]],
        ];
        // phpcs:enable
    }

    /**
     * @param ParsedArray $existing
     */
    #[DataProvider('getTypeTypeProvider')]
    public function testGetTypesReturnsTypes(FilterInterface $filter, array $existing, array $expected): void
    {
        $parser = new AllowListParser(1);
        $actual = $parser->getTypes($filter, $existing);
        self::assertSame($expected, $actual);
    }

    public static function getTypeTypeProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'strict list'     => [new AllowList(['list' => ['foo', 123], 'strict' => true]), [PsalmType::Int, PsalmType::String], [PsalmType::String, PsalmType::Int, PsalmType::Null]],
            'lax list'        => [new AllowList(['list' => [123, 1.23], 'strict' => false]), [PsalmType::Int, PsalmType::Float, PsalmType::String], [PsalmType::Int, PsalmType::Float, PsalmType::Null, PsalmType::String]],
            'strict existing' => [new AllowList(['list' => ['foo', 123], 'strict' => true]), [PsalmType::Int], [PsalmType::Int, PsalmType::Null]],
            'lax existing'    => [new AllowList(['list' => [123, 1.23], 'strict' => false]), [PsalmType::String], [PsalmType::Null, PsalmType::String]],
        ];
        // phpcs:enable
    }
}
