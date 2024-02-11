<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Filter;

use Kynx\Laminas\FormShape\Filter\DigitsVisitor;
use Laminas\Filter\Boolean;
use Laminas\Filter\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;

#[CoversClass(DigitsVisitor::class)]
final class DigitsVisitorTest extends AbstractFilterVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'invalid'     => [
                new Boolean(),
                [new TInt()],
                [new TInt()],
            ],
            'no digits'   => [
                new Digits(),
                [new TBool()],
                [new TBool()],
            ],
            'int'         => [
                new Digits(),
                [new TInt()],
                [new TNumericString()],
            ],
            'float'       => [
                new Digits(),
                [new TFloat()],
                [new TNumericString()],
            ],
            'string'      => [
                new Digits(),
                [new TString()],
                [new TNumericString()],
            ],
            'literal int' => [
                new Digits(),
                [new TLiteralInt(123)],
                [new TNumericString()],
            ],
        ];
    }

    protected function getVisitor(): DigitsVisitor
    {
        return new DigitsVisitor();
    }
}
