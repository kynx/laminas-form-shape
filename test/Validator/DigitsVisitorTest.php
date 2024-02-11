<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Laminas\Validator\Digits;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;

#[CoversClass(DigitsVisitor::class)]
final class DigitsVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'string'           => [
                new Digits(),
                [new TString(), new TNull()],
                [new TNumericString()],
            ],
            'non-empty-string' => [
                new Digits(),
                [new TNonEmptyString()],
                [new TNumericString()],
            ],
            'int'              => [
                new Digits(),
                [new TInt()],
                [new TInt()],
            ],
            'float'            => [
                new Digits(),
                [new TFloat()],
                [new TFloat()],
            ],
            'literal'          => [
                new Digits(),
                [new TLiteralInt(123)],
                [new TLiteralInt(123)],
            ],
        ];
    }

    protected static function getValidatorVisitor(): DigitsVisitor
    {
        return new DigitsVisitor();
    }
}
