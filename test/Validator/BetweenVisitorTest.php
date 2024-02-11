<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\BetweenVisitor;
use Laminas\Validator\Between;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;

#[CoversClass(BetweenVisitor::class)]
final class BetweenVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'numeric'        => [
                new Between(['min' => 0, 'max' => 1]),
                [new TInt(), new TFloat(), new TNull()],
                [new TIntRange(0, 1), new TFloat()],
            ],
            'numeric string' => [
                new Between(['min' => 0, 'max' => 1]),
                [new TString()],
                [new TNumericString()],
            ],
            'string'         => [
                new Between(['min' => 'a', 'max' => 'm']),
                [new TString()],
                [new TString()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): BetweenVisitor
    {
        return new BetweenVisitor();
    }
}
