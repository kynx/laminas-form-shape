<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\InflectorVisitor;
use Laminas\Filter\Inflector;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TMixed;
use Psalm\Type\Atomic\TString;

#[CoversClass(InflectorVisitor::class)]
final class InflectorVisitorTest extends AbstractFilterVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'int' => [
                new Inflector(),
                [new TMixed()],
                [new TString()],
            ],
        ];
    }

    protected function getVisitor(): InflectorVisitor
    {
        return new InflectorVisitor();
    }
}
