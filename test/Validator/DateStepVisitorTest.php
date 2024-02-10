<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use DateTime;
use DateTimeImmutable;
use Kynx\Laminas\FormShape\Validator\DateStepVisitor;
use Laminas\Validator\DateStep;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TIntRange;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;

#[CoversClass(DateStepVisitor::class)]
final class DateStepVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        $dateTime          = new TNamedObject(DateTime::class);
        $dateTimeImmutable = new TNamedObject(DateTimeImmutable::class);

        return [
            'datetime'           => [
                new DateStep(),
                [$dateTime, new TNull()],
                [$dateTime],
            ],
            'datetime immutable' => [
                new DateStep(),
                [$dateTimeImmutable],
                [$dateTimeImmutable],
            ],
            'float'              => [
                new DateStep(),
                [new TFloat()],
                [new TFloat()],
            ],
            'int'                => [
                new DateStep(),
                [new TInt()],
                [new TInt()],
            ],
            'negative int'       => [
                new DateStep(),
                [new TIntRange(null, -1)],
                [new TIntRange(null, -1)],
            ],
            'array'              => [
                new DateStep(),
                [new TArray([Type::getArrayKey(), Type::getMixed()])],
                [new TNonEmptyArray([Type::getArrayKey(), new Type\Union([new TInt(), new TNumericString()])])],
            ],
            'string'             => [
                new DateStep(),
                [new TString()],
                [new TNonEmptyString()],
            ],
            'positive int'       => [
                new DateStep(),
                [new TIntRange(1, null)],
                [new TIntRange(1, null)],
            ],
        ];
    }

    protected static function getValidatorVisitor(): DateStepVisitor
    {
        return new DateStepVisitor();
    }
}
