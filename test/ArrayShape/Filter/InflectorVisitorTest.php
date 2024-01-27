<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\ArrayShape\Filter;

use Kynx\Laminas\FormCli\ArrayShape\Filter\InflectorVisitor;
use Kynx\Laminas\FormCli\ArrayShape\Type\PsalmType;
use Laminas\Filter\Boolean;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\Inflector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InflectorVisitor::class)]
final class InflectorVisitorTest extends TestCase
{
    #[DataProvider('visitProvider')]
    public function testVisit(FilterInterface $filter, array $expected): void
    {
        $visitor = new InflectorVisitor();
        $actual  = $visitor->visit($filter, [PsalmType::String, PsalmType::Null]);
        self::assertSame($expected, $actual);
    }

    public static function visitProvider(): array
    {
        return [
            'invalid'   => [new Boolean(), [PsalmType::String, PsalmType::Null]],
            'inflector' => [new Inflector(), [PsalmType::String]],
        ];
    }
}
