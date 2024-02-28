<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormShape\Validator;

use Kynx\Laminas\FormShape\Validator\DigitsVisitor;
use Kynx\Laminas\FormShape\Validator\ExplodeVisitor;
use Kynx\Laminas\FormShape\ValidatorVisitorInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\Explode;
use PHPUnit\Framework\Attributes\CoversClass;
use Psalm\Type;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TBool;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Atomic\TNumericString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use Traversable;

#[CoversClass(ExplodeVisitor::class)]
final class ExplodeVisitorTest extends AbstractValidatorVisitorTestCase
{
    public static function visitProvider(): array
    {
        $digits    = new Digits();
        $validator = new Explode(['validator' => $digits]);

        return [
            'no validator'        => [
                new Explode(),
                [new TBool()],
                [new TBool(), new TArray([Type::getArrayKey(), new Union([new TString()])])],
            ],
            'array'               => [
                $validator,
                [new TArray([new Union([new TInt()]), new Union([new TString()])])],
                [new TArray([new Union([new TInt()]), new Union([new TNumericString()])])],
            ],
            'keyed array'         => [
                $validator,
                [new TKeyedArray(['a' => new Union([new TString(), new TNull()])])],
                [new TKeyedArray(['a' => new Union([new TNumericString()])])],
            ],
            'traversable'         => [
                $validator,
                [new TNamedObject(Traversable::class)],
                [
                    new TGenericObject(Traversable::class, [
                        new Union([
                            new TNumericString(),
                            new TFloat(),
                            new TInt(),
                        ]),
                    ]),
                    new TArray([Type::getArrayKey(), new Union([new TNumericString()])]),
                ],
            ],
            'generic traversable' => [
                $validator,
                [new TGenericObject(Traversable::class, [new Union([new TString()])])],
                [
                    new TGenericObject(Traversable::class, [new Union([new TNumericString()])]),
                    new TArray([Type::getArrayKey(), new Union([new TNumericString()])]),
                ],
            ],
            'no delimiter'        => [
                new Explode(['validator' => $digits, 'valueDelimiter' => null]),
                [new TString()],
                [new TNumericString(), new TArray([Type::getArrayKey(), new Union([new TNumericString()])])],
            ],
            'string'              => [
                $validator,
                [new TNonEmptyString()],
                [new TNonEmptyString(), new TArray([Type::getArrayKey(), new Union([new TNumericString()])])],
            ],
        ];
    }

    protected static function getValidatorVisitor(): ValidatorVisitorInterface
    {
        return new ExplodeVisitor([new DigitsVisitor()]);
    }
}
