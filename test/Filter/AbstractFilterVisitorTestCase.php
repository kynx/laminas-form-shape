<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\FilterVisitorInterface;
use KynxTest\Laminas\FormShape\Psalm\GetIdVisitor;
use Laminas\Filter\FilterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psalm\Type\Atomic;
use Psalm\Type\Union;

use function array_values;

abstract class AbstractFilterVisitorTestCase extends TestCase
{
    /**
     * @return non-empty-array<list{FilterInterface, non-empty-array<Atomic>, array<Atomic>}>
     */
    abstract protected static function visitProvider(): array;

    abstract protected function getVisitor(): FilterVisitorInterface;

    /**
     * @param non-empty-array<Atomic> $existing
     * @param array<Atomic> $expected
     */
    #[DataProvider('visitProvider')]
    public function testVisit(FilterInterface $filter, array $existing, array $expected): void
    {
        $visitor  = $this->getVisitor();
        $filtered = $visitor->visit($filter, new Union($existing));
        $actual   = array_values($filtered->getAtomicTypes());

        self::fixUnionIds($expected);
        self::fixUnionIds($actual);

        self::assertEquals($expected, $actual);
    }

    /**
     * Ensures types have called `getId()` on any sub-unions so side effects don't fail tests
     *
     * @param array<Atomic> $expected
     */
    protected static function fixUnionIds(array $expected): void
    {
        $getIdVisitor = new GetIdVisitor();
        foreach ($expected as $type) {
            /** @psalm-suppress UnusedMethodCall */
            $type->visit($getIdVisitor);
        }
    }
}
