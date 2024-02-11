<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\IsbnVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Isbn;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TString;

#[CoversClass(IsbnVisitor::class)]
final class IsbnVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'int, auto'    => [
                new Isbn(['type' => Isbn::AUTO]),
                [new TBool(), new TInt()],
                [new TIntRange(1000000000, 9799999999999)],
            ],
            'int, isbn10'  => [
                new Isbn(['type' => Isbn::ISBN10]),
                [new TBool(), new TInt()],
                [new TIntRange(1000000000, 9999999999)],
            ],
            'int, isbn13'  => [
                new Isbn(['type' => Isbn::ISBN13]),
                [new TBool(), new TInt()],
                [new TIntRange(9780000000000, 9799999999999)],
            ],
            'negative int' => [
                new Isbn(),
                [new TIntRange(null, -1)],
                [],
            ],
            'string'       => [
                new Isbn(),
                [new TString()],
                [new TNonEmptyString()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new IsbnVisitor();
    }
}
