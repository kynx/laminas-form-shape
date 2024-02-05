<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use Laminas\Filter\FilterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Union;

use function array_values;

abstract class AbstractFilterVisitorTestCase extends TestCase
{
    abstract protected function getVisitor(): FilterVisitorInterface;

    /**
     * @param non-empty-array<Atomic> $existing
     */
    #[DataProvider('visitProvider')]
    public function testVisit(FilterInterface $filter, array $existing, array $expected): void
    {
        $visitor  = $this->getVisitor();
        $filtered = $visitor->visit($filter, new Union($existing));
        $actual   = array_values($filtered->getAtomicTypes());
        self::assertEquals($expected, $actual);
    }
}
