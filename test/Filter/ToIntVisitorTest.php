<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\ToIntVisitor;
use Laminas\Filter\Boolean;
use Laminas\Filter\ToInt;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TString;

#[CoversClass(ToIntVisitor::class)]
final class ToIntVisitorTest extends AbstractFilterVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'invalid filter' => [new Boolean(), [new TString()], [new TString()]],
            'int'            => [new ToInt(), [new TString()], [new TString(), new TInt()]],
        ];
    }

    protected function getVisitor(): ToIntVisitor
    {
        return new ToIntVisitor();
    }
}
