<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use DateTimeInterface;
use Kynx\Laminas\FormShape\Validator\DateVisitor;
use Laminas\Validator\Date;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;

#[CoversClass(DateVisitor::class)]
final class DateVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        $dateTimeInterface = new TNamedObject(DateTimeInterface::class);

        return [
            'datetime' => [
                new Date(),
                [$dateTimeInterface],
                [$dateTimeInterface],
            ],
            'float'    => [
                new Date(),
                [new TFloat()],
                [new TFloat()],
            ],
            'int'      => [
                new Date(),
                [new TInt()],
                [new TInt()],
            ],
            'array'    => [
                new Date(),
                [new TArray([Type::getArrayKey(), Type::getMixed()])],
                [new TNonEmptyArray([Type::getArrayKey(), new Union([new TNumericString(), new TInt()])])],
            ],
            'string'   => [
                new Date(),
                [new TString()],
                [new TNonEmptyString()],
            ],
        ];
    }

    protected static function getValidatorVisitor(): DateVisitor
    {
        return new DateVisitor();
    }
}
