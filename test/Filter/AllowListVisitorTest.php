<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\AllowListVisitor;
use Kynx\Laminas\FormShape\Type\Literal;
use Kynx\Laminas\FormShape\Type\PsalmType;
use Kynx\Laminas\FormShape\Type\TypeUtil;
use Laminas\Filter\AllowList;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-import-type VisitedArray from TypeUtil
 */
#[CoversClass(AllowListVisitor::class)]
final class AllowListVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('getTypeLiteralProvider')]
    public function testGetTypesReturnsLiteral(FilterInterface $filter, array $existing, array $expected): void
    {
        $visitor = new AllowListVisitor();
        $actual  = $visitor->visit($filter, $existing);
        self::assertEquals($expected, $actual);
    }

    public static function getTypeLiteralProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid'            => [new Boolean(), [PsalmType::Int], [PsalmType::Int]],
            'empty list'         => [new AllowList(['list' => []]), [PsalmType::Bool], [PsalmType::Bool]],
            'strict list'        => [new AllowList(['list' => ['foo', 123], 'strict' => true]), [PsalmType::Int, PsalmType::String], [PsalmType::Null, new Literal(["foo", 123])]],
            'strict not literal' => [new AllowList(['list' => [1.23], 'strict' => true]), [PsalmType::Float], [PsalmType::Float, PsalmType::Null]],
            'lax list'           => [new AllowList(['list' => ['foo', 123], 'strict' => false]), [PsalmType::Int, PsalmType::String], [PsalmType::Null, new Literal(["foo", 123, "123"])]],
            'lax literal string' => [new AllowList(['list' => ['foo', 123], 'strict' => false]), [PsalmType::String], [PsalmType::Null, new Literal(["foo", "123"])]],
            'lax float'          => [new AllowList(['list' => [1.23], 'strict' => false]), [PsalmType::Float], [PsalmType::Float, PsalmType::Null]],
            'lax float string'   => [new AllowList(['list' => [1.23], 'strict' => false]), [PsalmType::String], [PsalmType::NumericString, PsalmType::Null]],
            'strict existing'    => [new AllowList(['list' => ['foo', 123], 'strict' => true]), [PsalmType::Int], [PsalmType::Null, new Literal([123])]],
            'lax existing'       => [new AllowList(['list' => ['foo', 123], 'strict' => false]), [PsalmType::String], [PsalmType::Null, new Literal(["foo", "123"])]],
        ];
        // phpcs:enable
    }

    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('getTypeTypeProvider')]
    public function testGetTypesReturnsTypes(FilterInterface $filter, array $existing, array $expected): void
    {
        $visitor = new AllowListVisitor(true, 1);
        $actual  = $visitor->visit($filter, $existing);
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

    public function testGetTypesDisallowsEmptyList(): void
    {
        $expected = [PsalmType::Null];
        $visitor  = new AllowListVisitor(false);
        $filter   = new AllowList(['list' => []]);
        $actual   = $visitor->visit($filter, [PsalmType::String]);
        self::assertSame($expected, $actual);
    }
}
