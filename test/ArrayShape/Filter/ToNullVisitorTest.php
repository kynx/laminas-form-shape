<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\ToNullVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Type\AbstractVisitedType;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToNull;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_values;

/**
 * @psalm-import-type VisitedArray from AbstractVisitedType
 */
#[CoversClass(ToNullVisitor::class)]
final class ToNullVisitorTest extends TestCase
{
    /**
     * @param VisitedArray $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(FilterInterface $filter, array $existing, array $expected): void
    {
        $visitor = new ToNullVisitor();
        $actual  = $visitor->visit($filter, $existing);
        self::assertSame($expected, array_values($actual));
    }

    public static function visitProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid' => [new Boolean(), [PsalmType::Int], [PsalmType::Int]],
            'zero'    => [new ToNull(['type' => 0]), [PsalmType::String], [PsalmType::String]],
            'none'    => [new ToNull(), [PsalmType::String], [PsalmType::NonEmptyString, PsalmType::Null]],
            'string'  => [new ToNull(['type' => ToNull::TYPE_STRING]), [PsalmType::String], [PsalmType::NonEmptyString, PsalmType::Null]],
            'int'     => [new ToNull(['type' => ToNull::TYPE_INTEGER]), [PsalmType::Int], [PsalmType::NegativeInt, PsalmType::PositiveInt, PsalmType::Null]],
            'bool'    => [new ToNull(['type' => ToNull::TYPE_BOOLEAN]), [PsalmType::Bool], [PsalmType::True, PsalmType::Null]],
            'all'     => [new ToNull(['type' => ToNull::TYPE_ALL]), [PsalmType::String], [PsalmType::NonEmptyString, PsalmType::Null]],
            'mixed'   => [new ToNull(['type' => ToNull::TYPE_BOOLEAN]), [PsalmType::String, PsalmType::Bool], [PsalmType::String, PsalmType::True, PsalmType::Null]],
        ];
        // phpcs:enable
    }
}
