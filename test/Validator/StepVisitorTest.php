<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\StepVisitor;
use Laminas\Validator\Step;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;

#[CoversClass(StepVisitor::class)]
final class StepVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        return [
            'float'  => [
                new Step(),
                [new TFloat(), new TNull()],
                [new TFloat()],
            ],
            'int'    => [
                new Step(),
                [new TInt()],
                [new TInt()],
            ],
            'string' => [
                new Step(),
                [new TString()],
                [new TNumericString()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): StepVisitor
    {
        return new StepVisitor();
    }
}
