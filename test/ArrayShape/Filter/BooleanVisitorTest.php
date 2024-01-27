<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\BooleanVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\AllowList;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BooleanVisitor::class)]
final class BooleanVisitorTest extends TestCase
{
    #[DataProvider('visitProvider')]
    public function testVisit(FilterInterface $filter, array $expected): void
    {
        $visitor = new BooleanVisitor();
        $actual  = $visitor->visit($filter, [PsalmType::String, PsalmType::Null]);
        self::assertSame($expected, $actual);
    }

    public static function visitProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'invalid filter' => [new AllowList(), [PsalmType::String, PsalmType::Null]],
            'casting'        => [new Boolean(['casting' => true]), [PsalmType::Bool]],
            'null'           => [new Boolean(['casting' => false, 'type' => Boolean::TYPE_NULL]), [PsalmType::String, PsalmType::Bool]],
        ];
        // phpcs:enable
    }
}
