<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\ToFloatVisitor;
use Laminas\Filter\Boolean;
use Laminas\Filter\ToFloat;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TString;

#[CoversClass(ToFloatVisitor::class)]
final class ToFloatVisitorTest extends AbstractFilterVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'invalid' => [new Boolean(), [new TString()], [new TString()]],
            'float'   => [new ToFloat(), [new TString()], [new TString(), new TFloat()]],
        ];
    }

    protected function getVisitor(): ToFloatVisitor
    {
        return new ToFloatVisitor();
    }
}
