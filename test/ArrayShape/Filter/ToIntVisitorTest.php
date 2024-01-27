<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\ToIntVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\ToInt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToIntVisitor::class)]
final class ToIntVisitorTest extends TestCase
{
    #[DataProvider('visitProvider')]
    public function testVisit(FilterInterface $filter, array $expected): void
    {
        $visitor = new ToIntVisitor();
        $actual  = $visitor->visit($filter, [PsalmType::String, PsalmType::Null]);
        self::assertSame($expected, $actual);
    }

    public static function visitProvider(): array
    {
        return [
            'invalid filter' => [new Boolean(), [PsalmType::String, PsalmType::Null]],
            'int'            => [new ToInt(), [PsalmType::String, PsalmType::Null, PsalmType::Int]],
        ];
    }
}
